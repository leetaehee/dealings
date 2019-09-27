<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 회원탈퇴 화면
	 */
	
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php'; // 현재 세션체크

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