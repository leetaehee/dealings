<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 마이페이지
	 */
	
	// 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	// 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	// 공통함수
	 include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	// 현재 세션체크
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php';
    
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php'; // adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php'; // adodb

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MileageClass.php'; // Class 파일

	// 템플릿에서 <title>에 보여줄 메세지 설정
	$title = TITLE_MY_CHARGE_LIST . ' | ' . TITLE_SITE_NAME;
    $returnUrl = SITE_DOMAIN.'/mypage.php'; // 리턴되는 화면 URL 초기화.

	$idx = $_SESSION['idx'];

	$mileageClass = new MileageClass($db); 
	$chargeList = $mileageClass->getMileageCharge($idx);

	if($chargeList==false){
		alertMsg($returnUrl, 1, '오류! 관리자에게 문의하세요');
	}

	$rocordCount = $chargeList->recordCount();

	ob_Start();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/my_charge_list.html.php';
	$output = ob_get_clean();


	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃
