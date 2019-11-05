<?php
	/**
	 * 로그인 기능(일반회원)
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	// Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/LoginClass.php';

	// Exception 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
        $alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// 일반회원 로그인 
		$loginClass = new LoginClass($db);

		// 유효성 검증 및 로그인실패시 이동 링크
		$returnUrl = SITE_DOMAIN . '/login.php';

		// injection, xss 방지코드
		$_POST['id'] = htmlspecialchars($_POST['id']);
		$_POST['password'] = htmlspecialchars($_POST['password']);
		$postData = $_POST;

		$loginValidator = $loginClass->checkLoginFormValidate($postData);
		if ($loginValidator['isValid'] === false) {
			throw new Exception($loginValidator['errorMessage']);
		}

		// 트랜잭션 시작
		$db->startTrans();

        $rMemberLoginQ = 'SELECT `id`,
							     `idx`,
							     `password`,
							     `name`,
							     `grade_code`,
							     `is_forcedEviction`,
							     `forcedEviction_date`,
                                 `join_date`,
							     `join_approval_date`,
							     `withdraw_date`
						  FROM `th_members`
						  WHERE `id` = ?
						  FOR UPDATE';

        // 로그인 정보 추출
        $rMemberLoginResult = $db->execute($rMemberLoginQ, $postData['id']);
        if ($rMemberLoginResult == false) {
            throw new RollbackException('로그인 정보를 조회하면서 오류가 발생했습니다.');
        }

        // 회원가입일
        $joinDate = $rMemberLoginResult->fields['join_date'];
        if ($joinDate == null) {
            throw new RollbackException('회원가입이 되어 있지 않은 계정입니다.');
        }

        // 회원 가입 후 메일을 승인한 일자
        $joinApprovalDate = $rMemberLoginResult->fields['join_approval_date'];
        if ($joinApprovalDate == null) {
            throw new RollbackException('회원가입 메일 승인을 하세요.');
        }

        // 탈퇴일
        $withdrawDate = $rMemberLoginResult->fields['withdraw_date'];
        if ($withdrawDate != null) {
            throw new RollbackException('해당 계정은 탈퇴되어서 접속 할 수 없습니다.');
        }

        // 강제탈퇴(접근차단)일
        $isForcedEviction = $rMemberLoginResult->fields['is_forcedEviction'];
        if ($isForcedEviction == 'Y') {
            throw new RollbackException('해당 계정은 접근이 차단되었습니다.');
        }

        // 회원 비밀번호
        $dbPassword = $rMemberLoginResult->fields['password'];
        if (password_verify($postData['password'], $dbPassword) == false) {
            throw new RollbackException('비밀번호를 확인하세요.');
        }

        // 회원 접속 기록 추가
        $cMemberLoginP = [
            'idx'=> $rMemberLoginResult->fields['idx'],
            'remote_addr'=> setEncrypt($_SERVER['REMOTE_ADDR']),
            'http_user_agent'=> setEncrypt($_SERVER['HTTP_USER_AGENT'])
        ];

        $cMemberLoginQ = 'INSERT INTO `th_access_ip` SET
                            `member_idx` = ?,
                            `access_ip` = ?,
                            `access_date` = CURDATE(),
                            `access_datetime` = NOW(),
                            `access_user_agent` = ?';

        $cMemberLoginResult = $db->execute($cMemberLoginQ, $cMemberLoginP);

        $memberLoginInsertId = $db->insert_id();
        if ($memberLoginInsertId < 1) {
            throw new RollbackException('회원 접속내역을 추가하면서 오류가 발생했습니다.');
        }

        // 세션 정보 추가
        $_SESSION['idx'] = $rMemberLoginResult->fields['idx'];
        $_SESSION['id'] = $rMemberLoginResult->fields['id'];
        $_SESSION['name'] = setDecrypt($rMemberLoginResult->fields['name']);
        $_SESSION['grade_code'] = $rMemberLoginResult->fields['grade_code'];

        $returnUrl = SITE_DOMAIN;

		$alertMessage = '로그인 성공하였습니다.';

		$db->completeTrans();
	} catch (RollbackException $e) {
		// 트랜잭션 문제가 발생했을 때
		$alertMessage = $e->getMessage();

		$db->failTrans();
		$db->completeTrans();
	} catch (Exception $e) {
		// 트랜잭션을 사용하지 않을 때
		$alertMessage = $e->getMessage();
    } finally {
        if  ($connection === true) {
			// 커넥션 닫음
            $db->close();
        }
		
        if (!empty($alertMessage)) {
            alertMsg($returnUrl, 1, $alertMessage);
        } else {
            alertMsg(SITE_DOMAIN, 0);
        }
    }