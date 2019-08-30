<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 회원가입 화면 
	 */
	
	// 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	// 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	// PDO 객체 생성
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/databaseConnection.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_JOIN_MENU . ' | ' . TITLE_SITE_NAME;
		
		// form 전송시 전달되는 URL.
		$actionUrl = MEMBER_PROCESS_ACTION . '/member_process.php';
		
		// 회원가입 모드- 'add', 회원수정- 'modi', 회원탈퇴-'del'
		$actionMode = 'add';
		
		ob_Start();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/join.html.php'; // 템플릿
		$output = ob_get_clean();
	}catch(Exception $e) {
		$output = DB_CONNECTION_ERROR_MESSAGE . $e->getMessage() . ', 위치: '.$e->getFile() . ':'.$e->getLine();
	}

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout.html.php'; // 전체 레이아웃

