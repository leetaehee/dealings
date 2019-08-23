<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 회원가입 완료 후 보여주는 화면
	 */

	include __DIR__.'/../configs/config.php'; // 환경설정
	include __DIR__.'/../messages/message.php'; // 메세지
	include __DIR__.'/../includes/session_check.php'; // 현재 세션체크

	try {
        include __DIR__.'/../includes/databaseConnection.php'; // PDO 객체 생성
		$title = TITLE_JOIN_COMPLETE_MENU.' | '.TITLE_SITE_NAME; // 템플릿에서 <title>에 보여줄 메세지 설정
		
		ob_Start();
		include __DIR__.'/../templates/join_complete.html.php'; // 템플릿
		$output = ob_get_clean();
	}catch(Exception $e) {
		$output = DB_CONNECTION_ERROR_MESSAGE.$e->getMessage().', 위치: '.$e->getFile().':'.$e->getLine();
	}

	include __DIR__ .'/../templates/layout.html.php'; // 전체 레이아웃
