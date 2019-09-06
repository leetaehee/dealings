<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 로그인 기능(일반회원)
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php'; // adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php'; // adodb

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/LoginClass.php'; // Class 파일

	$returnUrl = ''; // 리턴되는 화면 URL 초기화.
	$mode = isset($_POST['mode']) ?  $_POST['mode'] : $_GET['mode'];

	if ($mode == 'login') {
		// 일반회원 로그인 
		$loginClass = new LoginClass($db);

		// 유효성 검증 및 로그인실패시 이동 링크
		$returnUrl = SITE_DOMAIN.'/login.php'; 

		$postData = $_POST;
		$loginValidator = $loginClass->checkLoginFormValidate($postData);

		if ($loginValidator['isValid']==false) {
			alertMsg($returnUrl, 1,$loginValidator['errorMessage']);
		} else {
			$loginData = $loginClass->getIsLogin($postData['id']);

			if ($loginData->fields['withdraw_date']!=null) {
				alertMsg($returnUrl, 1, '해당 계정은 탈퇴되었습니다!');
			}

			if ($loginData->fields['is_forcedEviction']=='Y') {
				alertMsg($returnUrl, 1, '해당 계정은 차단되었습니다! 관리자에게 문의하세요');
			}
		
			if (password_verify($postData['password'], $loginData->fields['password'])) {
				$returnUrl = SITE_DOMAIN.'/imi.php'; // 로그인 성공 시 이동 링크

				$param = [
					$loginData->fields['idx'], 
					setEncrypt($_SERVER['REMOTE_ADDR']), 
					setEncrypt($_SERVER['HTTP_USER_AGENT'])
				];

				$insertResult = $loginClass->insertIP($param);

				if ($insertResult < 1) {
					alertMsg(SITE_DOMAIN, 1, 'IP테이블에 문제가 생겼습니다! 관리자에게 문의하세요');
				}

				$_SESSION['idx'] = $loginData->fields['idx'];
				$_SESSION['id'] = $loginData->fields['id'];
				$_SESSION['name'] = setDecrypt($loginData->fields['name']);
				$_SESSION['grade_code'] = $loginData->fields['grade_code'];
				$_SESSION['member_type'] = 'member';

				alertMsg($returnUrl, 1, '로그인 성공하였습니다!');
			}
			alertMsg($returnUrl, 1, '로그인 실패 했습니다.');
		}
	} else if ($mode == 'admin_login') {
		// 관리자 로그인

		$loginClass = new LoginClass($db);

		// 유효성 검증 및 로그인실패시 이동 링크
		$returnUrl = SITE_DOMAIN.'/admin/login.php'; 

		$postData = $_POST;
		$loginValidator = $loginClass->checkLoginFormValidate($postData);

		if ($loginValidator['isValid']==false) {
			alertMsg($returnUrl, 1,$loginValidator['errorMessage']);
		} else {
			$loginData = $loginClass->getIsAdminLogin($postData['id']);

			if ($loginData->fields['withdraw_date']!=null) {
				alertMsg($returnUrl, 1, '해당 계정은 탈퇴되었습니다!');
			}

			if ($loginData->fields['is_forcedEviction']=='Y') {
				alertMsg($returnUrl, 1, '해당 계정은 차단되었습니다! 슈퍼 관리자에게 문의하세요');
			}

			if (password_verify($postData['password'], $loginData->fields['password'])) {
				$returnUrl = SITE_DOMAIN.'/admin/imi.php'; // 로그인 성공 시 이동 링크

				$param = [
					$loginData->fields['idx'], 
					setEncrypt($_SERVER['REMOTE_ADDR']), 
					setEncrypt($_SERVER['HTTP_USER_AGENT'])
				];

				$insertResult = $loginClass->insertAdminIP($param);

				if ($insertResult < 1) {
					alertMsg(SITE_ADMIN_DOMAIN, 1, 'IP테이블에 문제가 생겼습니다! 관리자에게 문의하세요');
				}

				$_SESSION['mIdx'] = $loginData->fields['idx'];
				$_SESSION['id'] = $loginData->fields['id'];
				$_SESSION['name'] = setDecrypt($loginData->fields['name']);
				$_SESSION['is_superadmin'] = $loginData->fields['is_supradmin'];

				alertMsg($returnUrl, 1, '로그인 성공하였습니다!');
			}

			alertMsg($returnUrl, 1, '로그인 실패 했습니다.');
		}
	}
