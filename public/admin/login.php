<?php
	/**
	 * 로그인 화면 
	 */

	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';

	try {
		$title = TITLE_LOGIN_MENU . ' | ' . TITLE_ADMIN_SITE_NAME;
        $actionUrl = LOGIN_PROCESS_ACTION . '/admin_login.php';
        $JsTemplateUrl = JS_URL . '/admin/login.js';

		$alertMessage = '';

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/login.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	}

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout.html.php'; // 전체 레이아웃