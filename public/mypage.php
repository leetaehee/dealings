<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 마이페이지
	 */

	include __DIR__.'/../configs/config.php'; // 환경설정
	include __DIR__.'/../messages/message.php'; // 메세지
	include __DIR__.'/../includes/session_check.php'; // 현재 세션체크

	try {
        include __DIR__.'/../includes/databaseConnection.php'; // PDO 객체 생성
		$title = TITLE_MYPAGE_MENU.' | '.TITLE_SITE_NAME; // 템플릿에서 <title>에 보여줄 메세지 설정

		$action_url = ''; // form 전송시 전달되는 URL.
		$action_mode = ''; 

		$member_del_url = MEMBER_PROCESS_ACTION.'/member_process.php'; // 회원탈퇴 링크

		ob_Start();
		include __DIR__.'/../templates/mypage.html.php'; // 템플릿
		$output = ob_get_clean();
	}catch(Exception $e) {
		$output = DB_CONNECTION_ERROR_MESSAGE.$e->getMessage().', 위치: '.$e->getFile().':'.$e->getLine();
	}

	include __DIR__ .'/../templates/layout.html.php'; // 전체 레이아웃
