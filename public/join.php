<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 회원가입 화면 
	 */
	
	// 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	// 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_JOIN_MENU . ' | ' . TITLE_SITE_NAME;
		$alertMessage = '';
		$actionUrl = MEMBER_PROCESS_ACTION . '/member_process.php'; // form action url
		$ajaxUrl = MEMBER_PROCESS_ACTION . '/member_ajax_process.php'; // ajax url
		$JsTemplateUrl = JS_URL . '/join.js'; 
		$actionMode = 'add'; // 회원가입

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