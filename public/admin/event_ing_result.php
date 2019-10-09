<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 진행중인 이벤트 결과
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
		$title = TITLE_EVENT_ING_RESULT . ' | ' . TITLE_ADMIN_SITE_NAME;
		$actionUrl = EVENT_PROCEE_ACTION . '/issue_event_result.php';
		$returnUrl = SITE_ADMIN_DOMAIN.'/admin_event.php'; // 리턴되는 화면 URL 초기화
		$alertMessage = '';
		
		$IsReturnFeeProvider = false;

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// injection 방지
		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$getData = $_GET;

		$eventClass = new EventClass($db);
        
        $eventIdxParam = [
          'idx'=> $getData['idx'],
          'is_end'=> 'N'
        ];
		
		$eventData = $eventClass->getEventDataByIdx($eventIdxParam);
		if ($eventData === false) {
			throw new Exception('이벤트를 조회 하는 중에 오류가 발생했습니다.');
		}
		$eventIdx = $eventData->fields['idx'];
		$eventName = $eventData->fields['name'];
		$eventType = $eventData->fields['event_type'];
		$eventStartDate = $eventData->fields['start_date'];
		$eventEndDate = $eventData->fields['end_date'];
		$eventIsEnd = $eventData->fields['is_end'];

		if (empty($eventIdx)) {
			throw new Exception('이벤트를 조회할 수 없습니다.');
		}

		if ($today > $eventEndDate) {
			$IsReturnFeeProvider = true;
		}

		$historyParam = [
			'eventIdx'=> $eventIdx,
			'limit'=> 10,
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