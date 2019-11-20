<?php
	/**
	 * 특정기간 지날 경우 거래글 자동 삭지 (크론탭)
	 */

	$topDir = __DIR__ . '/../../..';

	// 공통
	include_once $topDir . '/configs/config.php';
	include_once $topDir . '/messages/message.php';
	include_once $topDir . '/includes/function.php';

	// adodb
	include_once $topDir . '/adodb/adodb.inc.php';
	include_once $topDir . '/includes/adodbConnection.php';
	
	// Class 파일
	include_once $topDir . '/class/DealingsClass.php'; 

	// Exception 파일
	include_once $topDir . '/Exception/RollbackException.php';

	try {
		$alertMessage = '';

		$dealingsClass = new DealingsClass($db);

		// 트랜잭션시작
        $db->startTrans();
		
		$rDeleteP = [
			'is_del'=>'N',
			'expiration_date'=> $today
		];

		$rDeleteQ = 'SELECT `idx`,
							`dealings_status`
					 FROM `th_dealings`
					 WHERE `is_del` = ?
					 AND `expiration_date` < ?
					 AND `dealings_status` IN (1,2)
					 FOR UPDATE';
				
		$rDeleteResult = $db->execute($rDeleteQ, $rDeleteP);
		if ($rDeleteResult === false) {
			throw new RollbackException('거래 삭제 가능한 데이터를 조회 하면서 오류가 발생했습니다.');
		}

		$rDeleteResultCount = $rDeleteResult->recordCount();
		if ($rDeleteResultCount < 1) {
			throw new RollbackException('거래 삭제 가능한 데이터가 존재하지 않습니다.');
		}

		foreach($rDeleteResult as $key => $value){
			$delData[] = [
				'dealings_status'=> $value['dealings_status'],
				'idx'=> $value['idx']
			];
		}

		for ($i = 0; $i < count($delData); $i++) {
			// 거래 상태 파라미터
			$dealingsStatusP = [
				'is_del'=> 'Y',
				'dealings_status'=> 6,
				'dealings_idx'=> $delData[$i]['idx']
			];

			$uDealingsQ = 'UPDATE `th_dealings` SET 
							`is_del` = ?,
							`dealings_status` = ?
						   WHERE `idx` = ?';

			$uDealingsResult = $db->execute($uDealingsQ, $dealingsStatusP);

			$dealingsResultAffectedRow = $db->affected_rows();
			if ($dealingsResultAffectedRow < 0) {
				throw new RollbackException('거래 삭제하는 중에 오류가 발생했습니다.');
			}

			$dealingsIdx = $delData[$i]['idx'];

			$rDealingsUserQ = 'SELECT COUNT(`idx`) cnt FROM `th_dealings_user` WHERE `dealings_idx` = ?';

			$rDealingsUserResult = $db->execute($rDealingsUserQ, $dealingsIdx);
			if ($rDealingsUserResult === false) {
				throw new RollbackException('거래 유저 테이블을 조회 하면서 오류가 발생했습니다.');
			}
			
			$isExistDealingsCount = $rDealingsUserResult->fields['cnt'];
			if ($isExistDealingsCount > 0) {
				$uDealingsUserP = [
					'dealings_status'=> 6,
					'idx'=> $dealingsIdx
				];

				$uDealingsUserQ = 'UPDATE `th_dealings_user` SET 
									`dealings_status` = ?,
									`dealings_date` = curdate()
								  WHERE `dealings_idx` = ?';

				$uDealingsUserResult = $db->execute($uDealingsUserQ, $uDealingsUserP);

				$dealingsUserAffectedRow = $db->affected_rows();
				if ($dealingsUserAffectedRow < 0) {
					throw new RollbackException('거래 유저 상태를 변경하는 중에 오류가 발생했습니다.');
				}
			}

			$cProcessP = [
				'idx'=> $dealingsIdx,
				'dealings_status'=> 6
			];

			$cProcessQ = 'INSERT INTO `th_dealings_process` SET
							`dealings_idx` = ?,
							`dealings_status_idx` = ?,
							`dealings_datetime` = now()';
			
			$cDealingsProcessResult = $db->execute($cProcessQ, $cProcessP);
			
			$dealingsProcessInsertId = $db->insert_id();
			if ($dealingsProcessInsertId < 1) {
				throw new RollbackException('거래 처리 과정을 생성하면서 오류가 발생했습니다.');
			}
		}

		$alertMessage = '삭제 예정중인 거래데이터가 지워졌습니다! 감사합니다. |';

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
            echo $alertMessage;
		}
    }