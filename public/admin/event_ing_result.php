<?php
	/**
	 * 진행중인 이벤트 결과
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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/EventClass.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_EVENT_ING_RESULT . ' | ' . TITLE_ADMIN_SITE_NAME;
		$actionUrl = EVENT_PROCEE_ACTION . '/issue_event_result.php';
		$returnUrl = SITE_ADMIN_DOMAIN.'/admin_event.php'; // 리턴되는 화면 URL 초기화
		
		$alertMessage = '';
		
		$isReturnFeeProvider = false;

		$eventClass = new EventClass($db);

		// injection 방지
		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$_GET['event_type'] = htmlspecialchars($_GET['event_type']);
		$getData = $_GET;

		$eventData = $CONFIG_EVENT_ARRAY[$getData['event_type']];
		if (empty($eventData['idx'])) {
			throw new Exception('이벤트를 조회 할 수 없습니다.');
		}

		$eventType = $eventData['event_type'];
		$eventName = $eventData['event_name'];
		$eventStartDate = $eventData['start_date'];
		$eventEndDate = $eventData['end_date'];
		$eventIsEnd = $eventData['is_end'];

		if ($today > $eventData['end_date']) {
			$isReturnFeeProvider = true;
		}
		
		$historyParam = [
			'event_type'=> $getData['event_type'],
			'limit'=> 10
		];

		$eventHistoryList = $eventClass->getEventHistoryList($historyParam);
		if ($eventHistoryList === false) {
			throw new Exception('이벤트 결과를 가져오면서 오류가 발생했습니다.');
		}
		$eventHistoryListCount = $eventHistoryList->recordCount();

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/event_ing_result.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_event.html.php';// 전체 레이아웃