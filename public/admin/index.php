<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 사이트 소개하는 메인화면
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_HOME_MENU .' | ' . TITLE_ADMIN_SITE_NAME;
		$alertMessage = '';

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/home.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout.html.php'; // 전체 레이아웃