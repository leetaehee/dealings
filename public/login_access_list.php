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

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/LoginClass.php'; // Class 파일

	// 템플릿에서 <title>에 보여줄 메세지 설정
	$title = TITLE_LOGIN_LIST . ' | ' . TITLE_SITE_NAME;
    $returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화.

    $loginClass = new LoginClass($db); 
	
	$param = [
			'idx'=>$_SESSION['idx'],
			'today'=>date('Y-m-d')
		];

	$loginAccessList = $loginClass->getLoginAccessList($param);

	if($loginAccessList==false) {
		alertMsg($returnUrl,1,'데이터를 조회할 수 없습니다');
	}

	$rocordCount = $loginAccessList->recordCount();

	ob_Start();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/login_access_list.html.php';
	$output = ob_get_clean();


	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃
