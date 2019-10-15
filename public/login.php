<?php
	/**
	 * 로그인 화면 
	 */

	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';

	try {
		$title = TITLE_LOGIN_MENU . ' | ' . TITLE_SITE_NAME; // 템플릿에서 <title>에 보여줄 메세지 설정
		$alertMessage = '';
		$actionUrl = LOGIN_PROCESS_ACTION . '/login.php'; // form 전송시 전달되는 URL.
		$JsTemplateUrl = JS_URL . '/login.js';

		$templateFileName = $_SERVER['DOCUMENT_ROOT'] . '/../templates/login.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg(SITE_DOMAIN,1,$alertMessage);
		}
	} 

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout.html.php'; // 전체 레이아웃