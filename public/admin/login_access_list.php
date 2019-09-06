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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php';
    
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php'; // adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php'; // adodb

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/LoginClass.php'; // Class 파일

	// 템플릿에서 <title>에 보여줄 메세지 설정
	$title = TITLE_ADMIN_LOGIN_LIST . ' | ' . TITLE_ADMIN_SITE_NAME;
    $returnUrl = SITE_ADMIN_DOMAIN; // 리턴되는 화면 URL 초기화.

    $loginClass = new LoginClass($db); 
	
	$param = [
			'idx'=>$_SESSION['mIdx'],
			'today'=>date('Y-m-d')
		];

	$adminLoginAccessList = $loginClass->getAdminLoginAccessList($param);

	if($adminLoginAccessList==false) {
		alertMsg($returnUrl,1,'데이터를 조회할 수 없습니다');
	}

	$rocordCount = $adminLoginAccessList->recordCount();

	ob_Start();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/login_access_list.html.php';
	$output = ob_get_clean();


	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_main.html.php'; // 전체 레이아웃
