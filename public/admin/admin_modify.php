<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 회원수정 화면
	 */
	
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php'; // 현재 세션체크

    include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php'; // adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php'; // adodb

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/AdminClass.php'; // Class 파일

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_ADMIN_MODIFY_MENU . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN.'/admin_page.php'; // 리턴되는 화면 URL 초기화.
		$alertMessage = '';

		$actionUrl = ADMIN_PROCESS_ACTION . '/modify_member.php'; // form action url
		$ajaxUrl = ADMIN_PROCESS_ACTION . '/admin_ajax_process.php'; // ajax url
		$JsTemplateUrl = JS_ADMIN_URL . '/join.js'; 

		$adminClass = new AdminClass($db);
		$idx = $_SESSION['mIdx'];

		$adminData = $adminClass->getAdminData($idx);
		if ($adminData === false) {
			throw new Exception('회원정보를 찾을 수 없습니다. 관리자에게 문의하세요.');
		}

		$adminId = $adminData->fields['id'];
		$adminName = setDecrypt($adminData->fields['name']);
		$adminEmail = setDecrypt($adminData->fields['email']);
		$adminPhone = setDecrypt($adminData->fields['phone']);
		$adminBirth = setDecrypt($adminData->fields['birth']);
		$adminSex = $adminData->fields['sex'];

		$adminSexMChecked = 'checked';
		$adminSexWChecked = '';
		if ($adminSex === 'M') {
			$adminSexMChecked = 'checked';
		} else {
			$adminSexWChecked = 'checked';
		}

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/join.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if ($connection==true) {
			$db->close();
		}

		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	}

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_main.html.php'; // 전체 레이아웃