<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 로그인 화면 
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지

	$title = TITLE_LOGIN_MENU . ' | ' . TITLE_SITE_NAME; // 템플릿에서 <title>에 보여줄 메세지 설정

	$actionUrl = LOGIN_PROCESS_ACTION . '/login_process.php'; // form 전송시 전달되는 URL.
	$actionMode = 'login'; 
	$JsTemplateUrl = JS_URL . '/login.js'; 

	ob_Start();
	// 템플릿
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/login.html.php';
	$output = ob_get_clean();

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout.html.php'; // 전체 레이아웃
