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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/EventClass.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MileageClass.php';

	// Exception 파일 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$returnUrl = SITE_ADMIN_DOMAIN . '/admin_event.php'; // 리턴되는 화면 URL 초기화.
        $alertMessage = '';
		$isUseForUpdate = true;

		if ($connection === false) {
           throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }
        
        $_POST['idx'] = htmlspecialchars($_POST['idx']);
        $postData = $_POST;

		$eventClass = new EventClass($db);
        $mileageClass = new MileageClass($db);
        $memberClass= new MemberClass($db);

		$db->startTrans();
        
		$eventData = $CONFIG_EVENT_ARRAY['판매'];

		if($eventData['idx'] != $postData['idx']){
			throw new RollbackException('이벤트를 조회 할 수 없습니다.');
		}

		$historyParam = [
			'event_type'=> '판매',
			'limit'=> 10,
		];
        
        $eventHistoryList = $eventClass->getEventHistoryList($historyParam, $isUseForUpdate);
		if ($eventHistoryList === false) {
			throw new Exception('이벤트 결과를 가져오면서 오류가 발생했습니다.');
		}
        
        $chargeTitle = $eventName.'_판매환급!!';
        
        foreach ($eventHistoryList as $key => $value) {
           $eventCost = $value['event_cost'];
           $memberIdx = $value['member_idx'];
           $mileageType = 8;
            
			if ($eventCost > 0) {
				$chargeParam = [
                    'idx'=> $memberIdx,
                    'account_bank'=> '아이엠아이',
                    'account_no'=> setEncrypt($chargeTitle),
                    'charge_cost'=> $eventCost,
                    'spare_cost'=> $eventCost,
                    'charge_name'=>'관리자',
                    'mileageType'=> $mileageType,
                    'dealings_date'=> date('Y-m-d'),
                    'charge_status'=> 3
                ];
               
				// 충전정보 추가
				$insertPaybackChargeResult = $mileageClass->insertMileageCharge($chargeParam);
				if ($insertPaybackChargeResult < 1) {
                   throw new RollbackException('판매 환급으로 마일리지 충전 실패하였습니다.');
				}

				$chageParamMileageParam = [
                   'charge_cost'=> $eventCost,
                   'idx'=> $memberIdx
				];
				$updatePaybackResult = $memberClass->updateMileageCharge($chageParamMileageParam); // 마일리지변경
				if ($updatePaybackResult < 1) {
                   throw new RollbackException('회원 마일리지 정보 수정 실패하였습니다.');
				}

				$memberPaybackMileageType = $mileageClass->getMemberMileageTypeIdx($memberIdx, $isUseForUpdate);
				if ($memberPaybackMileageType == false) {
					$chageMileageTypeParam = [
						'memberIdx'=> $memberIdx, 
						'eventCost'=> $eventCost
					];
                  
					$mileagePayTypeInsert = $mileageClass->mileageTypeInsert($mileageType, $chageMileageTypeParam);
					if ($mileagePayTypeInsert < 1) {
						throw new RollbackException('마일리지 유형별 합계 삽입 실패 하였습니다.');
					} 
				} else {
					$chageMileageTypeParam = [
						'eventCost'=> $eventCost,
						'memberIdx'=> $memberIdx
					];
					$mileagePayTypeUpdate = $mileageClass->mileageTypeChargeUpdate($mileageType, $chageMileageTypeParam);
					if ($mileagePayTypeUpdate < 1) {
						throw new RollbackException('마일리지 유형별 합계 정보 수정 실패 하였습니다.');
					}
				}
			}
        }

        $returnUrl = SITE_ADMIN_DOMAIN . '/event_list.php';
		$alertMessage = '이벤트가 정상적으로 종료가 되었습니다.';

		//$db->completeTrans();
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