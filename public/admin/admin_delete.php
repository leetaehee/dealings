<?php
	/**
	 * 관리자 탈퇴 시 비밀번호 입력하는 화면 
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php';

	try {
		$title = TITLE_ADMIN_PAGE_DEL_MENU . ' | ' . TITLE_ADMIN_SITE_NAME;
        $actionUrl = ADMIN_PROCESS_ACTION . '/delete_member.php';
        $JsTemplateUrl = JS_ADMIN_URL . '/admin_delete.js';

        $alertMessage = '';

		$memberIdx = htmlspecialchars($_GET['idx']);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/admin_delete.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	}
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_main.html.php'; // 전체 레이아웃