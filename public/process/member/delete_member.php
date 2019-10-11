<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 회원탈퇴
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/mailer.lib.php'; // PHP메일보내기

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

    // Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/LoginClass.php';

	// Exception 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
        $alertMessage = ''; // 메세지
        
        if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		if (isset($_GET['password'])) {
			$password = htmlspecialchars($_GET['password']);
		} else {
			$password = htmlspecialchars($_POST['password']);
		}

		$idx = $_SESSION['idx'];
		
		// 유효성검증 실패시, 리턴
		$returnUrl = SITE_DOMAIN.'/member_delete.php?idx='.$idx; 

		$memberClass = new MemberClass($db);
		$loginClass = new LoginClass($db);

		$param = [
			'idx'=> $idx,
			'password'=> $password
		];

		// 트랜잭션 시작
		$db->startTrans();

		$checkLoginData = $loginClass->checkPasswordByUser($param, $isUseForUpdate);
        if ($checkLoginData === false) {
            throw new RollbackException('패스워드 체크 오류! 관리자에게 문의하세요.');
        }
		
		if ($checkLoginData === null) {
            throw new RollbackException('패스워드를 확인하세요!');
        }

		$updateResult = $memberClass->deleteMember($idx);
		if ($updateResult < 1) {
			throw new RollbackException('회원 탈퇴 오류! 관리자에게 문의하세요!');
		}
         
		$returnUrl = SITE_DOMAIN;
	
		$alertMessage = '정상적으로 탈퇴되었습니다. 이용해주셔서 감사합니다.';
		session_destroy();

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