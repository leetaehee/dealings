<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 회원가입 화면 
	 */
	
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_JOIN_MENU . ' | ' . TITLE_ADMIN_SITE_NAME;
		$alertMessage = '';
		
		$actionUrl = ADMIN_PROCESS_ACTION . '/admin_process.php'; // form action url
		$ajaxUrl = ADMIN_PROCESS_ACTION . '/admin_ajax_process.php'; // ajax url
		$JsTemplateUrl = JS_ADMIN_URL . '/join.js'; 
		$actionMode = 'add'; // 회원가입

		$adminId = $adminName = $adminEmail = $adminPhone = $adminBirth = $adminSex = '';
		$adminSexMChecked = 'checked';
		$adminSexWChecked = '';

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/join.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	}
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout.html.php'; // 전체 레이아웃