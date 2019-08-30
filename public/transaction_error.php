<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 사이트 메인화면으로서 로그인 여부에 따라 화면을 다르게 보여준다.
	 */

	// 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	// 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	// PDO 객체 생성
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/databaseConnection.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_TRANSACTION_ERROR_PAGE . ' | ' . TITLE_SITE_NAME;
		
		ob_Start();
		// 템플릿
		include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/transaction_error.html.php';
		$output = ob_get_clean();
	}catch(Exception $e) {
		$output = DB_CONNECTION_ERROR_MESSAGE . $e->getMessage() . ', 위치: ' . $e->getFile() . ':' . $e->getLine();
	}

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout.html.php'; // 전체 레이아웃
