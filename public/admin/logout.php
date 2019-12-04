<?php
	/**
	 * 로그아웃 
	 */

	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php';

	try {
        $title = TITLE_LOGOUT_MENU . ' | ' . TITLE_ADMIN_SITE_NAME;

		$alertMessage = '로그아웃 되었습니다.'; 

		// 세션 제거
		unset($_SESSION['mIdx']);
		unset($_SESSION['mId']);
		unset($_SESSION['mName']);
		unset($_SESSION['mIs_superadmin']);
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		alertMsg(SITE_ADMIN_DOMAIN,'1',$alertMessage);
	}