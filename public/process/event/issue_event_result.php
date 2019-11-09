<?php
	/**
	 * 판매 이벤트 종료 후 환급하기  
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

    // Class 파일
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MileageClass.php';

	// Exception 파일 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$returnUrl = SITE_ADMIN_DOMAIN . '/admin_event.php';
        $alertMessage = '';

		if ($connection === false) {
           throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }
        
        $_POST['idx'] = htmlspecialchars($_POST['idx']);
        $postData = $_POST;

        $mileageClass = new MileageClass($db);

		$db->startTrans();

        $eventData = $CONFIG_EVENT_ARRAY['판매'];

        // 이벤트 정보가 올바른지 체크.
        if ($eventData['idx'] != $postData['idx']) {
            throw new RollbackException('이벤트 정보가 올바르지 않습니다.');
        }

        // 이벤트 순위를 출력함 (1-10등)
        $rEventHistoryP = [
            'event_type'=> '판매',
            'limit'=> 10,
        ];

        $rEventHistoryQ = 'SELECT `participate_count`,
                                  `event_cost`,
                                  `event_type`,
                                  `member_idx`
                           FROM `th_event_history`
                           WHERE `event_type` = ?
                           ORDER BY `event_cost` DESC, `participate_count` DESC
                           LIMIT ?
                           FOR UPDATE';

        $rEventHistoryResult = $db->execute($rEventHistoryQ, $rEventHistoryP);

        if ($rEventHistoryResult === false) {
            throw new RollbackException('이벤트 순위를 조회하면서 오류가 발생했습니다.');
        }

        foreach ($rEventHistoryResult as $key => $value) {
            $eventCost = $value['event_cost'];

            // 이벤트 참여 시 금액이 0보다 큰 경우
            if ($eventCost > 0) {
                // 이벤트 환급시 보여지는 제목
                $chargeTitle = '이벤트 판매환급!!';

                $memberIdx = $value['member_idx'];
                $mileageType = 8;

                // 환급금액 계산
                $eventReturnFee = round(($eventCost*$CONFIG_EVENT_SELL_RETRUN_FEE[$key+1])/100);

                // 수수료 환급하기
                $chargeParamGroup = [
                    'charge_param' => [
                        'member_idx'=> $memberIdx,
                        'charge_infomation'=> '아이엠아이',
                        'charge_account_no'=> setEncrypt($chargeTitle),
                        'charge_cost'=> $eventReturnFee,
                        'spare_cost'=> $eventReturnFee,
                        'charge_name'=> '관리자',
                        'mileage_idx'=> $mileageType,
                        'charge_date'=> $today,
                        'charge_status'=> 3
                    ],
                    'dealings_idx'=> 0,
                    'dealings_status'=> 4,
                    'mileageType'=> $mileageType,
                    'mode'=> 'event'
                ];

                // 판매 이벤트 수수료 환급(충전)
                $chargeResult = $mileageClass->chargeMileageProcess($chargeParamGroup);
                if ($chargeResult['result'] === false) {
                    throw new RollbackException($chargeResult['resultMessage']);
                }
            }
        }

        $returnUrl = SITE_ADMIN_DOMAIN . '/event_list.php';

		$alertMessage = '이벤트가 정상적으로 종료가 되었습니다.';

		$db->completeTrans();
	} catch (RollbackException $e) {
		// 트랜잭션 문제가 발생했을 때
		$alertMessage = $e->getMessage();

		$db->failTrans();
		$db->completeTrans();
	} catch (Exception $e) {
		// 트랜잭션을 사용하지 않을 때
		$alertMessage = $e->getMessage();
    } finally {
        if  ($connection === true) {
            $db->close();
        }
        if (!empty($alertMessage)) {
            alertMsg($returnUrl, 1, $alertMessage);
        } else {
            alertMsg(SITE_ADMIN_DOMAIN, 0);
        }
    }