<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 사이트 메인화면으로서 로그인 여부에 따라 화면을 다르게 보여준다.
	 */

	include __DIR__.'/../configs/config.php'; // 환경설정
	include __DIR__.'/../messages/message.php'; // 메세지

	try {
        include __DIR__.'/../includes/databaseConnection.php'; // PDO 객체 생성
		$title = TITLE_HOME_MENU.' | '.TITLE_SITE_NAME; // 템플릿에서 <title>에 보여줄 메세지 설정
		
		ob_Start();
		include __DIR__.'/../templates/home.html.php'; // 템플릿
		$output = ob_get_clean();
	}catch(Exception $e) {
		$output = DB_CONNECTION_ERROR_MESSAGE.$e->getMessage().', 위치: '.$e->getFile().':'.$e->getLine();
	}

	include __DIR__ .'/../templates/layout.html.php'; // 전체 레이아웃
