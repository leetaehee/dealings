<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 마이페이지
	 */
	
	// 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	// 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	// 공통함수
	 include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	// 현재 세션체크
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php';
	// PDO 객체 생성
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/databaseConnection.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_MYPAGE_MENU . ' | ' . TITLE_SITE_NAME;

		$actionUrl = $actionMode = '';  // form 전송시 전달되는 URL.

		// 회원탈퇴 링크
		$memberDelUrl = MEMBER_PROCESS_ACTION . '/memberDel_process.php';

		ob_Start();

		// 일반회원인지 체크 	
		$isSessionPass = false;
		if (isset($_SESSION['member_type']) && $_SESSION['admin_type']!='admin'){
            $isSessionPass = true; 
		}

		// 템플릿
		include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/mypage.html.php';
		$output = ob_get_clean();
	}catch(Exception $e) {
		$output = DB_CONNECTION_ERROR_MESSAGE . $e->getMessage() . ', 위치: ' . $e->getFile() . ':' . $e->getLine();
	}

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout.html.php'; // 전체 레이아웃
