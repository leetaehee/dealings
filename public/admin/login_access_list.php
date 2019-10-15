<?php
	/**
	 * 로그인내역
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php';
    
	// adodb
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	// Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/LoginClass.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_ADMIN_LOGIN_LIST . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN; // 리턴되는 화면 URL 초기화.
		$alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$param = [
			'idx'=>$_SESSION['mIdx'],
			'today'=>date('Y-m-d')
		];
		$loginClass = new LoginClass($db);

		$adminLoginAccessList = $loginClass->getAdminLoginAccessList($param);
		if($adminLoginAccessList === false) {
			throw new Exception('로그인 내역을 가져오다가 오류 발생! 관리자에게 문의하세요');
		}

		$rocordCount = $adminLoginAccessList->recordCount();

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/login_access_list.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if ($connection === true) {
			$db->close();
		}

		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	}
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_main.html.php'; // 전체 레이아웃