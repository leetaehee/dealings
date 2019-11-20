<?php
	/**
	 * 휴대전화 충전
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php';

	try {
		$title = TITLE_PHONE_MILEGE_CHARGE . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN;

		$alertMessage = '';

		$actionUrl = MILEAGE_PROCESS_ACTION . '/phone_charge.php'; // form action url
		$JsTemplateUrl = JS_URL . '/phone_charge.js';
		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/phone_charge.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃