<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 로그인 화면 
	 */

	include __DIR__.'/../configs/config.php'; // 환경설정
	include __DIR__.'/../messages/message.php'; // 메세지

	try {
        include __DIR__.'/../includes/databaseConnection.php'; // PDO 객체 생성
		$title = TITLE_LOGIN_MENU.' | '.TITLE_SITE_NAME; // 템플릿에서 <title>에 보여줄 메세지 설정

		$action_url = MEMBER_PROCESS_ACTION.'/session_process.php'; // form 전송시 전달되는 URL.
		$action_mode = 'login'; // 
		
		ob_Start();
		include __DIR__.'/../templates/login.html.php'; // 템플릿
		$output = ob_get_clean();
	}catch(Exception $e) {
		$output = DB_CONNECTION_ERROR_MESSAGE.$e->getMessage().', 위치: '.$e->getFile().':'.$e->getLine();
	}

	include __DIR__ .'/../templates/layout.html.php'; // 전체 레이아웃
