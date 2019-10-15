<?php
	/**
	 * 관리자 정보 조회
	 */

	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php';
    
	// adodb
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	// Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/AdminClass.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_ADMIN_MENU . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN; // 리턴되는 화면 URL 초기화.
		$alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$adminClass = new AdminClass($db);
		$idx = $_SESSION['mIdx'];
		
		$adminData = $adminClass->getAdminData($idx);
		if ($adminData === false) {
			throw new Exception('로그인 된 관리자 정보 가져오면 오류가 발생했습니다.');
		}

		$adminDeleteUrl = SITE_ADMIN_DOMAIN . '/admin_delete.php?idx=' . $idx; // 회원탈퇴 
		$adminModifyUrl = SITE_ADMIN_DOMAIN . '/admin_modify.php?idx=' . $idx; // 회원수정 

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/admin_page.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if ($connection === true) {
			$db->close();
		}

		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	}
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_main.html.php'; // 전체 레이아웃