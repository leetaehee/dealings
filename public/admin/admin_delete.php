<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 관리자 탈퇴 시 비밀번호 입력하는 화면 
	 */
	
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php'; // 현재 세션체크

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_ADMIN_PAGE_DEL_MENU . ' | ' . TITLE_ADMIN_SITE_NAME;
		$alertMessage = ''; 
		$actionUrl = ADMIN_PROCESS_ACTION . '/delete_member.php';
		
		$JsTemplateUrl = JS_ADMIN_URL . '/admin_delete.js'; 
		$idx = htmlspecialchars($_GET['idx']);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/admin_delete.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	}
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_main.html.php'; // 전체 레이아웃