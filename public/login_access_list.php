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

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_LOGIN_LIST . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화.
		$alertMessage = '';
		
		$param = [
				'idx'=>$_SESSION['idx'],
				'today'=>date('Y-m-d')
			];
		$loginClass = new LoginClass($db);

		$loginAccessList = $loginClass->getLoginAccessList($param);
		if ($loginAccessList === false) {
			throw new Exception('로그인 접속내역 조회 오류! 관리자에게 문의하세요.');
		}
		$rocordCount = $loginAccessList->recordCount();
		
		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/login_access_list.html.php';
	} catch (Exception $e) {
		if ($connection == true) {
			$alertMessage = $e->getMessage();
		}
	} finally {
		if ($connection == true) {
			$db->close();
		}

		if (!empty($alertMessage)) {
			alertMsg(SITE_DOMAIN,1,$alertMessage);
		}
	}

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃