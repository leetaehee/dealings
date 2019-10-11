<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 판매 이벤트 종료 후 환급하기  
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수

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

		if ($connection === false) {
           throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }
        
        $_POST['idx'] = htmlspecialchars($_POST['idx']);
        $postData = $_POST;

		$eventClass = new EventClass($db);
        $mileageClass = new MileageClass($db);
        $memberClass= new MemberClass($db);

		$db->startTrans();
        
        $eventIdxParam = [
          'idx'=> $postData['idx'],
          'is_end'=> 'N'
        ];
		
		$eventData = $eventClass->getEventDataByIdx($eventIdxParam, $isUseForUpdate);
		if ($eventData === false) {
			throw new Exception('이벤트를 조회 하는 중에 오류가 발생했습니다.');
		}
        $eventIdx = $eventData->fields['idx'];
        $eventName = $eventData->fields['name'];
        
        if (empty($eventIdx)) {
			throw new Exception('이벤트를 조회할 수 없습니다.');
		}
        
        $resultCount = $eventClass->getCheckEventResultData($eventIdx, $isUseForUpdate);
        if ($resultCount === false) {
            throw new RollbackException('이벤트 결과 테이블 중복검사하는 중에 오류가 발생했습니다.');
        }
        
        if ($resultCount > 0) {
            throw new RollbackException('이미 이벤트 결과 테이블에 등록되어있습니다.');
        }

		$historyParam = [
			'eventIdx'=> $eventIdx,
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
                    'account_bank'=>'아이엠아이',
                    'account_no'=> setEncrypt($chargeTitle),
                    'charge_cost'=> $eventCost,
                    'spare_cost'=> $eventCost,
                    'charge_name'=>'관리자',
                    'mileageType'=> $mileageType,
                    'dealings_date'=>date('Y-m-d'),
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
				
				/*
					$eventResultParam = [
						'event_idx'=> $eventIdx,
						'member_idx'=> $memberIdx,
						'event_cost'=> $eventCost,
						'event_rate'=> $CONFIG_EVENT_SELL_RETRUN_FEE[$key+1],
						'grade'=>$key+1
					];
				   
					$insertEventResult = $eventClass->insertEventResult($eventResultParam);
					if ($insertEventResult < 1) {
						throw new RollbackException('이벤트 결과에 입력 중에 오류가 발생했습니다.');
					}
				*/
			}
        }

		$eventIseEndParam = [
			'is_end'=> 'Y',
			'event_idx'=> $eventIdx
		];

		$updateEventIsEndResult = $eventClass->updateEventIsEnd($eventIseEndParam);
		if ($updateEventIsEndResult < 1) {
			throw new RollbackException('이벤트 종료여부를 수정하면서 오류가 발생했습니다.');
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