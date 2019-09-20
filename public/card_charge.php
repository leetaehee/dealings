<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 신용카드 충전
	 */
	
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php'; // 현재 세션체크

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_CARD_MILEGE_CHARGE . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화
		$alertMessage = '';

		$actionUrl = MILEAGE_PROCESS_ACTION . '/mileage_process.php'; // form action url
		$JsTemplateUrl = JS_URL . '/card_charge.js'; 
		$actionMode = 'charge'; // 충전모드
		$mileageType = 1;
		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/card_charge.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg(SITE_DOMAIN,1,$alertMessage);
		}
	}

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃