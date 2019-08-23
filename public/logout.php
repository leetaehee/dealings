<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 로그아웃 
	 */

	include __DIR__.'/../configs/config.php'; // 환경설정
	include __DIR__.'/../messages/message.php'; // 메세지
	include __DIR__.'/../includes/session_check.php'; // 현재 세션체크

	try {
        include __DIR__.'/../includes/databaseConnection.php'; // PDO 객체 생성
		$title = TITLE_LOGOUT_MENU.' | '.TITLE_SITE_NAME; // 템플릿에서 <title>에 보여줄 메세지 설정

		// 세션 제거 
		session_destroy();
		
		// 메인페이지이동
		header('location:'.SITE_DOMAIN);
	}catch(Exception $e) {
		$output = DB_CONNECTION_ERROR_MESSAGE.$e->getMessage().', 위치: '.$e->getFile().':'.$e->getLine();
	}
