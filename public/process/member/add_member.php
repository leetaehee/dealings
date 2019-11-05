<?php
	/**
	 * 회원가입 
	 */

	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/mailer.lib.php';

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

    // Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php';

	// Exception 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
        $alertMessage = ''; // 메세지

		$memberClass = new MemberClass($db);
        
        if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// 폼 데이터 받아서 유효성 검증
		$returnUrl = SITE_DOMAIN.'/join.php'; // 유효성검증 실패시, 리턴 UTL

		// injection, xss 방지코드
		$_POST['isOverlapEmail'] = htmlspecialchars($_POST['isOverlapEmail']);
		$_POST['isOverlapPhone'] = htmlspecialchars($_POST['isOverlapPhone']);
		$_POST['id'] = htmlspecialchars($_POST['id']);
		$_POST['password'] = htmlspecialchars($_POST['password']);
		$_POST['repassword'] = htmlspecialchars($_POST['repassword']);
		$_POST['name'] = htmlspecialchars($_POST['name']);
		$_POST['email'] = htmlspecialchars($_POST['email']);
		$_POST['phone'] = htmlspecialchars($_POST['phone']);
		$_POST['birth'] = htmlspecialchars($_POST['birth']);
		$_POST['sex'] = htmlspecialchars($_POST['sex']);
		$postData = $_POST;
		
		$resultMemberValidCheck = $memberClass->checkMemberFormValidate($postData);
		if ($resultMemberValidCheck['isValid'] == false) {
			throw new Exception($resultMemberValidCheck['errorMessage']);
		}

		$db->startTrans();

		$rMemberP = [
			'phone'=> setEncrypt($postData['phone']),
			'email'=> setEncrypt($postData['email']),
			'id'=> $postData['id']
		];

        // 계정 중복확인
        $rMemberQ = 'SELECT COUNT(`id`) cnt 
                          FROM `th_members`
                          WHERE `phone` = ? OR `email` = ? OR `id` = ?';

        $rMemberChkResult = $db->execute($rMemberQ, $rMemberP);
        if ($rMemberChkResult === false) {
            throw new RollbackException('계정 중복 검사를 하면서 오류가 발생했습니다.');
        }

        $accountOverlapCount = $rMemberChkResult->fields['cnt'];
        if ($accountOverlapCount > 0) {
            throw new RollbackException('아이디/이메일/핸드폰 번호는 중복 될 수 없습니다.');
        }
		
		$cMemberP = [
			'id'=> $postData['id'],
			'password'=> password_hash($postData['password'], PASSWORD_DEFAULT),
			'email'=> setEncrypt($postData['email']),
			'name'=> setEncrypt($postData['name']),
			'phone'=> setEncrypt($postData['phone']),
			'sex'=> $postData['sex'],
			'birth'=> setEncrypt($postData['birth'])
		];

        // 계정 추가
        $cMemberQ = 'INSERT INTO `th_members` SET
					  `id` = ?,
					  `grade_code` = 3,
					  `password` = ?,
					  `email` = ?,
					  `name` = ?,
					  `phone` = ?,
					  `sex` = ?,
					  `birth` = ?,
					  `join_date` = CURDATE()
					';

        $cMemberResult = $db->execute($cMemberQ, $cMemberP);

        $memberInsertId = $db->insert_id();
        if ($memberInsertId < 1) {
            throw new RollbackException('회원가입을 하면서 오류가 발생하였습니다.');
        }

		$returnUrl = SITE_DOMAIN . '/join_complete.php'; // 회원가입 화면 URL 지정(가입완료화면)

		$_SESSION['tmp_idx'] = 't_' . $memberInsertId; // 임시세션

		$approvalUrl = SITE_DOMAIN.'/join_approval.php?idx=' . $memberInsertId;
		$content = '<a href='.$approvalUrl.'>메일승인하러가기</a>';

		//메일발송
		mailer(MAIL_SENDER, MAIL_ADDRESS, $postData['email'], MAIL_TITLE, $content);

		$alertMessage = '회원 가입이 되었습니다. 이메일을 확인하세요';

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
            $db->close();
        }
		
        if (!empty($alertMessage)) {
            alertMsg($returnUrl, 1, $alertMessage);
        } else {
            alertMsg(SITE_DOMAIN, 0);
        }
    }