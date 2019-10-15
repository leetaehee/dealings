<?php
	/*
	 * 신용카드 충전
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_CARD_MILEGE_CHARGE . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화
		$alertMessage = '';

		$actionUrl = MILEAGE_PROCESS_ACTION . '/card_charge.php';
		$JsTemplateUrl = JS_URL . '/card_charge.js';
		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/card_charge.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg(SITE_DOMAIN,1,$alertMessage);
		}
	}

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃