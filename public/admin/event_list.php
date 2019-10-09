<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 진행중인 이벤트 현황
	 */
	
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; //메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php'; // 세션체크
	
	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	// Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/EventClass.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_EVENT_STATUS . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN.'/admin_event.php'; // 리턴되는 화면 URL 초기화
		$alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$eventClass = new EventClass($db);

		$eventListParam = [
			'is_end'=> 'N'
		];
		
		$eventList = $eventClass->getEventList($eventListParam);
		if ($eventList === false) {
			throw new Exception('진행중인 이벤트 목록을 가져오면서 오류가 발생했습니다.');
		}
		$eventListCount = $eventList->recordCount();

		// 이벤트 결과 저장 보여주는 페이지
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