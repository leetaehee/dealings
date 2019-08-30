<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 로그아웃 
	 */

	// 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	// 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	// 공용함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	// 현재 세션체크
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php';
	// PDO 객체 생성
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/databaseConnection.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_LOGOUT_MENU . ' | ' . TITLE_SITE_NAME;

		// 세션 제거 
		session_destroy();
		
		// 메인페이지이동
		header('location:' . SITE_DOMAIN);
	}catch(Exception $e) {
		$output = DB_CONNECTION_ERROR_MESSAGE . $e->getMessage() . ', 위치: ' . $e->getFile() . ':' . $e->getLine();
	}