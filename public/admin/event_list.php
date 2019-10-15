<?php
	/**
	 * 진행중인 이벤트 현황
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_EVENT_STATUS . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN.'/admin_event.php'; // 리턴되는 화면 URL 초기화
		$alertMessage = '';

		// 현재 진행중인 이벤트
		$eventListCount = count($CONFIG_EVENT_ARRAY);

		// 이벤트 결과 보여주는 페이지
		$issueEventResultURL = SITE_ADMIN_DOMAIN . '/event_ing_result.php'; 

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/event_list.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_event.html.php';// 전체 레이아웃