<?php
	/**
	 * 회원탈퇴 화면
	 */

	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_MYPAGE_DEL_MENU . ' | ' . TITLE_SITE_NAME;
		$alertMessage = '';
		$actionUrl = MEMBER_PROCESS_ACTION . '/delete_member.php'; // form action url
		$JsTemplateUrl = JS_URL . '/member_delete.js';

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/member_delete.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg(SITE_DOMAIN,1,$alertMessage);
		}
	}

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃