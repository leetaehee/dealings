<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 사이트 메인화면으로서 로그인 여부에 따라 화면을 다르게 보여준다.
	 */

	// 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	// 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';

	// 템플릿에서 <title>에 보여줄 메세지 설정
	$title = TITLE_HOME_MENU .' | ' . TITLE_ADMIN_SITE_NAME;
	
	ob_Start();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/home.html.php'; // 템플릿
	$output = ob_get_clean();

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout.html.php'; // 전체 레이아웃
