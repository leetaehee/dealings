<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 로그아웃 
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공용함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php'; // 현재 세션체크

	// 템플릿에서 <title>에 보여줄 메세지 설정
	$title = TITLE_LOGOUT_MENU . ' | ' . TITLE_SITE_NAME;

	try {
		$alertMessage = '로그아웃 되었습니다.'; 

		// 세션 제거 
		//session_destroy();
		unset($_SESSION['idx']);
		unset($_SESSION['id']);
		unset($_SESSION['name']);
		unset($_SESSION['grade_code']);
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		alertMsg(SITE_DOMAIN,'1',$alertMessage);
	}