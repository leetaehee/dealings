<?php
	/**
	 * 회원가입 화면 
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; 

	try {
		$title = TITLE_JOIN_MENU . ' | ' . TITLE_SITE_NAME;
		$alertMessage = '';

		$actionUrl = MEMBER_PROCESS_ACTION . '/add_member.php'; // form action url
		$ajaxUrl = MEMBER_PROCESS_ACTION . '/member_ajax_process.php'; // ajax url
		$JsTemplateUrl = JS_URL . '/join.js';

		$userId = $userName = $userEmail = $userPhone = $userBirth = $userSex = '';
		$userSexMChecked = 'checked';
		$userSexWChecked = '';

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/join.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg(SITE_DOMAIN,1,$alertMessage);
		}
	} 

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout.html.php'; // 전체 레이아웃