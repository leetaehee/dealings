<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 로그인 기능(일반회원)
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	// Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/LoginClass.php';

	// Exception 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {        
		$alertMessage = ''; // 메세지

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// 관리자 로그인
		$loginClass = new LoginClass($db);

		// 유효성 검증 및 로그인실패시 이동 링크
		$returnUrl = SITE_DOMAIN.'/admin/login.php'; 

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

		$loginData = $loginClass->getIsAdminLogin($postData['id']);

		if ($loginData === false){
			throw new RollbackException('로그인 실패 했습니다.');
		}

		if ($loginData->fields['withdraw_date'] != null) {
			throw new RollbackException('해당 계정은 탈퇴되었습니다!');
		}

		if ($loginData->fields['is_forcedEviction'] === 'Y') {
			throw new RollbackException('해당 계정은 차단되었습니다! 슈퍼 관리자에게 문의하세요');
		}

		if (!password_verify($postData['password'], $loginData->fields['password'])) {
			throw new RollbackException('비밀번호를 확인 하세요');
		}

		$returnUrl = SITE_DOMAIN.'/admin/imi.php'; // 로그인 성공 시 이동 링크

		$param = [
			$loginData->fields['idx'], 
			setEncrypt($_SERVER['REMOTE_ADDR']), 
			setEncrypt($_SERVER['HTTP_USER_AGENT'])
		];

		$insertResult = $loginClass->insertAdminIP($param);
		if ($insertResult < 1) {
			throw new RollbackException('IP테이블에 문제가 생겼습니다! 관리자에게 문의하세요.');
		}

		$_SESSION['mIdx'] = $loginData->fields['idx'];
		$_SESSION['mId'] = $loginData->fields['id'];
		$_SESSION['mName'] = setDecrypt($loginData->fields['name']);
		$_SESSION['mIs_superadmin'] = $loginData->fields['is_superadmin'];

		$alertMessage = '로그인 성공하였습니다!';

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