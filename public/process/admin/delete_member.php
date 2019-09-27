<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 관리자 회원탈퇴
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/mailer.lib.php'; // PHP메일보내기

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

    // Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/AdminClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/LoginClass.php';

	// Exception 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$alertMessage = '';
		$returnUrl = SITE_ADMIN_DOMAIN;

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		if (isset($_GET['password'])) {
			$password = htmlspecialchars($_GET['password']);
		} else {
			$password = htmlspecialchars($_POST['password']);
		}

		$idx = $_SESSION['mIdx'];

		$adminClass = new AdminClass($db);
        $loginClass = new LoginClass($db);

		$isNewData = false;

		// 유효성검증 실패시, 리턴 UTL
		$returnUrl = SITE_ADMIN_DOMAIN.'/admin_delete.php?idx='.$idx;

        $param = [
			'idx'=> $idx,
			'password'=> $password
		];

		$db->startTrans();

		$checkLoginData = $loginClass->checkPasswordByAdmin($param);
	
		if ($checkLoginData === false) {
			throw new RollbackException('패스워드를 가져오다가 오류가 발생하였습니다.');
		}

		if ($checkLoginData === null) {
			throw new RollbackException('패스워드를 확인하세요!');
		}
		
		$returnUrl = SITE_ADMIN_DOMAIN;

		$updateResult = $adminClass->deleteAdmin($idx);
		if ($updateResult < 1) {
			throw new RollbackException('회원 탈퇴 오류가 발생했습니다.');
		}

		session_destroy();
		$alertMessage = '정상적으로 탈퇴되었습니다. 이용해주셔서 감사합니다.';
		
		$db->commitTrans();
		$db->completeTrans();
    } catch (RollbackException $e) {
		// 트랜잭션 문제가 발생했을 때
		$alertMessage = $e->errorMessage();
		$db->rollbackTrans();
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