<?php
	/**
	 * 이벤트 메인화면 
	 */

	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php';

	try {
		$title = TITLE_EVENT_MAIN . ' | ' . TITLE_ADMIN_SITE_NAME;
		$alertMessage = '';

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/admin_event.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	}

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_event.html.php'; // 전체 레이아웃