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

		// 관리자 로그인
		$loginClass = new LoginClass($db);

		// 유효성 검증 및 로그인실패시 이동 링크
		$returnUrl = SITE_ADMIN_DOMAIN . '/login.php';

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

        $rAdminLoginQ = 'SELECT `id`,
                                `idx`,
                                `password`,
                                `name`,
                                `is_forcedEviction`,
                                `forcedEviction_date`,
                                `join_approval_date`,
                                `withdraw_date`,
                                `is_superadmin`
                         FROM `th_admin`
                         WHERE `id` = ?
                         FOR UPDATE';

        // 로그인 정보 추출
        $rAdminLoginResult = $db->execute($rAdminLoginQ, $postData['id']);
        if ($rAdminLoginResult === false) {
            throw new RollbackException('로그인 데이터를 조회하면서 오류가 발생했습니다.');
        }

        // 탈퇴여부 확인
        $withdrawDate = $rAdminLoginResult->fields['withdraw_date'];
        if ($withdrawDate != null) {
            throw new RollbackException('해당 계정은 탈퇴되었습니다.');
        }

        // 접근차단 여부 확인
        $isForcedEviction = $rAdminLoginResult->fields['is_forcedEviction'];
        if ($isForcedEviction == 'Y') {
            throw new RollbackException('해당 계정은 접근이 차단되었습니다.');

        }

        // 사용자가 입력한 비밀번호 확인
        $dbPassword = $rAdminLoginResult->fields['password'];
        if (password_verify($postData['password'], $dbPassword) == false) {
            throw new RollbackException('패스워드를 확인하세요.');
        }

        // 관리자 접속 기록 추가
        $cAdminLoginP = [
            'idx'=> $rAdminLoginResult->fields['idx'],
            'remote_addr'=> setEncrypt($_SERVER['REMOTE_ADDR']),
            'http_user_agent'=> setEncrypt($_SERVER['HTTP_USER_AGENT'])
        ];

        $cAdminLoginQ = 'INSERT INTO `th_admin_access_ip` SET
						  `admin_idx` = ?,
						  `access_ip` = ?,
						  `access_date` = CURDATE(),
						  `access_datetime` = NOW(),
						  `access_user_agent` = ?';

        $cAdminLoginResult = $db->execute($cAdminLoginQ, $cAdminLoginP);

        $adminLoginInsertId = $db->insert_id();
        if ($adminLoginInsertId < 1) {
            throw new RollbackException('관리자 접속내역을 추가하면서 오류가 발생했습니다.');
        }

        // 세션 정보 추가
        $_SESSION['mIdx'] = $rAdminLoginResult->fields['idx'];
        $_SESSION['mId'] = $rAdminLoginResult->fields['id'];
        $_SESSION['mName'] = setDecrypt($rAdminLoginResult->fields['name']);
        $_SESSION['mIs_superadmin'] = $rAdminLoginResult->fields['is_superadmin'];

        $returnUrl = SITE_ADMIN_DOMAIN;

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