<?php
	/**
	 * 가상계좌 충전
	 */

	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php';

	try {
		$title = TITLE_VIRTUAL_MILEGE_CHARGE . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN;

		$alertMessage = '';

		$actionUrl = MILEAGE_PROCESS_ACTION . '/virtual_account_charge.php';
		$ajaxUrl = VIRTUAL_ACCOUNT_PROCESS_ACTION . '/virtual_account_ajax.php';
		$JsTemplateUrl = JS_URL . '/virtual_account.js';

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/virtual_account_charge.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg(SITE_DOMAIN,1,$alertMessage);
		}
	}
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃