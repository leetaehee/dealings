<?php
	/**
	 * 회원가입 완료 후 보여주는 화면
	 */

	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';

	try {
		$title = TITLE_JOIN_COMPLETE_MENU . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN;

		if (isset($_SESSION['tmp_idx'])) {
			unset($_SESSION['tmp_idx']);
		} else {
			alertMsg($returnUrl, 1, '정상적인 경로가 아닙니다.'); 
		}

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/join_complete.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	}
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout.html.php'; // 전체 레이아웃