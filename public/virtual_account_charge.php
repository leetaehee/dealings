<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 가상계좌 충전
	 */
	
	// 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	// 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	// 공통함수
	 include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	// 현재 세션체크
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php';

	// 템플릿에서 <title>에 보여줄 메세지 설정
	$title = TITLE_VIRTUAL_MILEGE_CHARGE . ' | ' . TITLE_SITE_NAME;
    $returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화

	$actionUrl = MILEAGE_PROCESS_ACTION . '/mileage_process.php'; // form action url
	$ajaxUrl = VIRTUAL_ACCOUNT_PROCESS_ACTION . '/virtual_account_ajax_process.php'; // ajax url
	$JsTemplateUrl = JS_URL . '/virtual_account.js'; 
	$actionMode = 'charge'; // 충전모드
	$mileage_type = 5;

	$idx = $_SESSION['idx'];

	ob_Start();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/virtual_account_charge.html.php';
	$output = ob_get_clean();

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃