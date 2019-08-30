<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 로그인 화면 
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/databaseConnection.php'; // PDO 객체 생성

	try {
		$title = TITLE_LOGIN_MENU . ' | ' . TITLE_SITE_NAME; // 템플릿에서 <title>에 보여줄 메세지 설정

		$actionUrl = MEMBER_PROCESS_ACTION . '/session_process.php'; // form 전송시 전달되는 URL.
		$actionMode = 'login'; // 
		
		ob_Start();
		// 템플릿
		include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/login.html.php';
		$output = ob_get_clean();
	}catch(Exception $e) {
		$output = DB_CONNECTION_ERROR_MESSAGE . $e->getMessage() . ', 위치: ' . $e->getFile() . ':' . $e->getLine();
	}

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout.html.php'; // 전체 레이아웃
