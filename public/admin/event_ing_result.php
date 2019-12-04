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

	try {
		$title = TITLE_EVENT_ING_RESULT . ' | ' . TITLE_ADMIN_SITE_NAME;
        $returnUrl = SITE_ADMIN_DOMAIN . '/admin_event.php';
		$actionUrl = EVENT_PROCEE_ACTION . '/issue_event_result.php';
		
		$alertMessage = '';
		
		$isReturnFeeProvider = false;

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

		// 이벤트 히스토리 조회
		$rEventHistoryP = [
			'event_type'=> $getData['event_type'],
			'limit'=> 10
		];

		$rEventHistoryQ = 'SELECT `participate_count`,
							      `event_cost`,
							      `event_type`,
							      `member_idx`
		                   FROM `th_event_history`
		                   WHERE `event_type` = ?
		                   ORDER BY `event_cost` DESC, `participate_count` DESC
		                   LIMIT ?';

		$rEventHistoryResult = $db->execute($rEventHistoryQ, $rEventHistoryP);
		if ($rEventHistoryResult === false) {
		    throw new Exception('이벤트 히스토리를 조회하면서 오류가 발생했습니다.');
        }

		// 이벤트 히스토리 내역 추가
		$rEventHistoryData = [];

		foreach ($rEventHistoryResult as $key => $value) {

		    $participateCount = $value['participate_count'];
		    $eventCost = $value['event_cost'];
		    $eventType = $value['event_type'];
		    $memberIdx = $value['member_idx'];

		    // 이벤트 참여한 유저 정보 조회
            $rMemberQ = 'SELECT `name`
                         FROM `th_members`
                         WHERE `idx` = ?';

            $rMemberResult = $db->execute($rMemberQ, $memberIdx);
            if ($rMemberResult === false) {
                throw new Exception('이벤트 참여자를 조회하면서 오류가 발생했습니다.');
            }

            $name = $rMemberResult->fields['name'];

            // 이벤트 환급률 조회
            $returnFee = $CONFIG_EVENT_SELL_RETRUN_FEE[$key+1];
            // 이벤트 환급금액 계산
            $returnFeeCost = ($eventCost * $returnFee) / 100;

            $rEventHistoryData[] = [
                'seq'=> ($key+1),
                'participate_count'=> $participateCount,
                'event_cost'=> number_format($eventCost),
                'event_type'=> $eventType,
                'member_idx'=> $memberIdx,
                'name'=> setDecrypt($name),
                'return_fee'=> number_format($returnFee),
                'return_fee_cost'=> number_format($returnFeeCost)
            ];
        }

        $rEventHistoryDataCount = count($rEventHistoryData);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/event_ing_result.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_event.html.php';// 전체 레이아웃