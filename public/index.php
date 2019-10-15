<?php
	/**
	 * 사이트 메인화면으로서 로그인 여부에 따라 화면을 다르게 보여준다.
	 */

	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_HOME_MENU .' | ' . TITLE_SITE_NAME;
		$alertMessage = '';

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/home.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg(SITE_DOMAIN,1,$alertMessage);
		}
	}

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout.html.php'; // 전체 레이아웃