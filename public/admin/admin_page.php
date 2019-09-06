<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 관리자 정보
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

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/AdminClass.php'; // Class 파일

	// 템플릿에서 <title>에 보여줄 메세지 설정
	$title = TITLE_ADMIN_MENU . ' | ' . TITLE_ADMIN_SITE_NAME;
    $returnUrl = SITE_ADMIN_DOMAIN; // 리턴되는 화면 URL 초기화.

	
	$adminClass = new AdminClass($db);
    $idx = $_SESSION['mIdx'];

	
    $adminData = $adminClass->getAdminData($idx);

    if ($adminData==false) {
        alertMsg($returnUrl,1,'오류입니다! 관리자에게 문의하세요.');
    }

    $adminDeleteUrl = SITE_ADMIN_DOMAIN . '/admin_delete.php?idx=' . $idx; // 회원탈퇴 
    $adminModifyUrl = SITE_ADMIN_DOMAIN . '/admin_modify.php?idx=' . $idx; // 회원수정 

	ob_Start();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/admin_page.html.php';
	$output = ob_get_clean();


	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_main.html.php'; // 전체 레이아웃