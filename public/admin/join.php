<?php
	/**
	 * 회원가입 화면 
	 */
	
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지

	try {
		$title = TITLE_JOIN_MENU . ' | ' . TITLE_ADMIN_SITE_NAME;
        $actionUrl = ADMIN_PROCESS_ACTION . '/add_member.php';
        $ajaxUrl = ADMIN_PROCESS_ACTION . '/admin_ajax_process.php';
        $JsTemplateUrl = JS_ADMIN_URL . '/join.js';

		$alertMessage = '';

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