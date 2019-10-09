<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 
	 *	1. 거래(판매,구매)글은 5일이 지난 경우 삭제
	 *  2. imi_dealings에 expiration_date 컬럼을 이용할 것(오늘 날짜보다 작으면 삭제)
	 *  3. 거래유저 테이블 및 처리과정 남길것
	 *  4. 거래대기,결제대기에 대해 처리할것 
	 */

	$topDir = __DIR__.'/../../..';

	include_once $topDir . '/configs/config.php'; // 환경설정
	include_once $topDir . '/messages/message.php'; // 메세지
	include_once $topDir . '/includes/function.php'; // 공통함수

	// adodb
	include_once $topDir . '/adodb/adodb.inc.php';
	include_once $topDir . '/includes/adodbConnection.php';
	
	// Class 파일
	include_once $topDir . '/class/DealingsClass.php'; 

	// Exception 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$today = date('Y-m-d');

        $db->startTrans(); // 트랜잭션시작

        $dealingsClass = new DealingsClass($db);

		$dealingsDeleteList = $dealingsClass->getDealingsDeleteList();
        $dealingsDeleteCount = $dealingsDeleteList->recordCount();
        if ($dealingsDeleteCount > 0) {
			// 삭제되는 거래 데이터를 배열로 변환하기
            $dealingsData = $dealingsClass->getDealingsDeleteData($dealingsDeleteList);
            if ($dealingsData === false) {
                throw new RollbackException('데이터를 배열로 저장하면서 오류가 발생했습니다. |');
            }
			
			for ($i = 0; $i < count($dealingsData); $i++) {
				$param = [
					'dealings_status'=>6,
					'idx'=>$dealingsData[$i]['idx']
				];

				$updateParam = [
					'is_del'=>'Y',
					'dealings_status'=>6,
					'idx'=>$dealingsData[$i]['idx']
				];

				$insertParam = [
					'idx'=>$dealingsData[$i]['idx'],
					'dealings_status'=>6

				];

				$dealingsIdx = $dealingsData[$i]['idx'];

				$updateDealingsStatusResult = $dealingsClass->updateDealingsDeleteStatus($updateParam);
				if ($updateDealingsStatusResult < 1) {
					throw new RollbackException('거래 데이터 상태 변경 중에 오류가 발생했습니다. |');
				}

				$dealingsExistCount = $dealingsClass->isExistDealingsIdx($dealingsIdx);
				if ($dealingsExistCount === false) {
					throw new RollbackException('거래 유저를 조회하면서 오류가 발생했습니다. |');
				}

				if($dealingsExistCount > 0) {
					$updateDealingsUserResult = $dealingsClass->updateDealingsUser($param);
					if ($updateDealingsUserResult < 1) {
						throw new RollbackException('거래 유저 상태 변경 시 오류가 발생했습니다. |');
					}
				}

				$insertProcessResult = $dealingsClass->insertDealingsProcess($insertParam);
				if ($insertProcessResult < 1) {
					throw new RollbackException('거래 처리과정 생성 실패 하였습니다. |');
				}

				$alertMessage = '삭제 예정중인 거래데이터가 지워졌습니다! 감사합니다. |';
		
				$db->completeTrans();
			}
		} else {
			$alertMessage = '삭제할 데이터가 존재하지 않습니다. |';
		}
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
            echo $alertMessage;
		}
    }