<?php
	/**
	 *  마일리지 유효기간 지날 시 소멸 (crontab)
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
	include_once $topDir . '/class/MileageClass.php';

	// Exception 파일
	include_once $topDir . '/Exception/RollbackException.php';

    try {
		$alertMessage = '';

		$mileageClass = new MileageClass($db);

        $db->startTrans();

		$rChargesQ = 'SELECT `member_idx`,
							 `mileage_idx`,
							 `charge_account_no`,
							 `charge_infomation`,
							 `charge_name`,
							 `charge_status`,
							 `idx`,
							 `spare_cost`,
							 `expiration_date`
					  FROM `imi_mileage_charge`
					  WHERE `mileage_idx` <> 5
					  AND `charge_status` = 3
					  AND `expiration_date` < ?
					  ORDER BY `member_idx` ASC, `expiration_date` ASC
					  FOR UPDATE';
		
		$rChargeResult = $db->execute($rChargesQ, $today);
		if ($rChargeResult === false) {
			throw new RollbackException('소멸 마일리지를 조회하는 중에 오류가 발생했습니다.');
		}

		$rChargeResultCount = $rChargeResult->recordCount();
		if ($rChargeResultCount < 1) {
			throw new RollbackException('소멸 마일리지가 존재하지 않습니다.');
		}

		// 배열로 데이터저장
		foreach($rChargeResult as $key => $value){
			$expirationData[] = [
				'member_idx'=>$value['member_idx'],
				'mileage_idx'=>$value['mileage_idx'],
				'charge_account_no'=>$value['charge_account_no'],
				'charge_infomation'=>$value['charge_infomation'],
				'charge_name'=>$value['charge_name'],
				'charge_status'=>4,
				'process_date'=>$value['expiration_date'],
				'charge_idx'=>$value['idx'],
				'spare_cost'=>$value['spare_cost'],
			];
		}

		// 마일리지 상태 변경
		for ($i = 0; $i < count($expirationData); $i++) {
			$uChargeMileageP = [
				'spare_cost'=> $expirationData[$i]['spare_cost'],
				'use_cost'=> $expirationData[$i]['spare_cost'],
				'charge_idx'=> $expirationData[$i]['charge_idx']
			];

			$uChargesMileageQ = 'UPDATE `imi_mileage_charge` SET
								  `spare_cost` = `spare_cost` - ?,
								  `use_cost` = `use_cost` + ?
								  WHERE `idx` = ?';

			$uChargeMileageResult = $db->execute($uChargesMileageQ, $uChargeMileageP);
			$chargeMileageAffectedRow = $db->affected_rows();

			if ($chargeMileageAffectedRow < 1) {
				throw new RollbackException('마일리지 상태를 변경하면서 오류가 발생했습니다.');
			}
		}

		// 마일리지 변동내역 추가
		for ($i = 0; $i < count($expirationData); $i++) {
			$cMileageChangeQ = 'INSERT INTO `imi_mileage_change` SET
									`member_idx` = ?,
									`mileage_idx` = ?,
									`charge_account_no` = ?,
									`charge_infomation` = ?,
									`charge_name` = ?,
									`charge_status` = ?,
									`process_date` = ?,
									`charge_idx` = ?,
									`charge_cost` = ?
								';

			$cMileageChangeResult = $db->execute($cMileageChangeQ, $expirationData[$i]);
			
			$mileageChangeInsertId = $db->insert_id(); 
			if ($mileageChangeInsertId < 1) {
				throw new RollbackException('마일리지 변동내역을 추가하면서 오류가 발생했습니다.');
			}
		}

		// 유효기간이 지난 모든 회원의 소멸금액 가져오기
		$rAllMemberMileageQ = 'SELECT `member_idx`,
									  ifnull(sum(`charge_cost`),0) charge_cost
							   FROM `imi_mileage_charge`
							   WHERE `mileage_idx` <> 5
							   AND `charge_status` = 3
							   AND `expiration_date` < ?
							   GROUP BY `member_idx`
							   ORDER BY `member_idx`';
			
		$rAllMemberMileageResult = $db->execute($rAllMemberMileageQ, $today);
		if ($rAllMemberMileageResult === false) {
			throw new RollbackException('회원의 소멸 마일리지 합계를 조회하면서 오류가 발생했습니다.');
		}

		// 유효기간이 지난 모든 회원에 마일리지에서 소멸마일리지 차감하기
		foreach ($rAllMemberMileageResult as $key => $value) {
			$uMileageP = [
				'mileage'=> $value['charge_cost'],
				'member_idx'=> $value['member_idx']
			];

			$uMileageQ = 'UPDATE `imi_members` SET
							`mileage` = `mileage` - ? 
						   WHERE `idx` = ?';
			
			$uMileageResult = $db->execute($uMileageQ, $uMileageP);

			$mileageAffectedRow = $db->affected_rows();
			if ($mileageAffectedRow < 1) {
				throw new RollbackException('회원의 마일리지를 차감하면서 오류가 발생했습니다');
			}
		}

		// 유효기간이 지난 회원에 대하여 마일리지 타입별로 조회
		$rMileageTypeQ = 'SELECT `member_idx`,
								 `mileage_idx`,
								 ifnull(sum(`charge_cost`),0) charge_cost
						  FROM `imi_mileage_charge`
						  WHERE `mileage_idx` <> 5
						  AND `expiration_date` < ?
						  AND `charge_status` = 3
						  GROUP BY `member_idx`, `mileage_idx`';

		$rMileageTypeResult = $db->execute($rMileageTypeQ, $today);
		if ($rMileageTypeResult === false) {
			throw new RollbackException('회원 마일리지 유형 합계 조회하면서 오류가 발생했습니다.');
		}

		// 유효기간이 지난 회원의 마일리지 타입 수정 
		foreach ($rMileageTypeResult as $key => $value) {
			$mileageIdx = $value['mileage_idx'];
            $chargeCost = $value['charge_cost'];
            $memberIdx = $value['member_idx'];

			$rTypeSumQ = 'SELECT `idx` 
						  FROM `imi_mileage_type_sum` 
						  WHERE `member_idx` = ?';

			$rTypeSumResult = $db->execute($rTypeSumQ, $memberIdx);
			if ($rTypeSumResult === false) {
				throw new RollbackException('회원 마일리지 타입 합계를 조회 하면서 오류가 발생했습니다.');
			}

			$typeSumIdx = $rTypeSumResult->fields['idx'];

			// 마일리지 타입 컬럼명 추출
			$colName = $CONFIG_MILEAGE_TYPE_COLUMN[$mileageIdx];
			
			if (empty($typeSumIdx)) {
				$cTypeSumP = [
					'member_idx'=> $memberIdx,
					'charge_cost'=> $chargeCost
				];

				$cTypeSumQ = "INSERT INTO `imi_mileage_type_sum` SET
								`member_idx` = ?,
								`{$colName}` = `{$colName}` + ?";

				$cTypeSumResult = $db->execute($cTypeSumQ, $cTypeSumP);
				
				$typeSumInsertId = $db->insert_id();
				if ($typeSumInsertId < 1) {
					throw new RollbackException('회원 마일리지 유형 합계를 추가하면서 오류가 발생했습니다.');
				}
			} else {
				$uTypeSumP = [
					'charge_cost'=> $chargeCost,
					'member_idx'=> $memberIdx
				];

				$uTypeSumQ = "UPDATE `imi_mileage_type_sum` SET
								`{$colName}` = `{$colName}` - ?
								WHERE `member_idx` = ?";

				$uTypeSumResult = $db->execute($uTypeSumQ, $uTypeSumP);
				
				$uTypeSumAffectedRow = $db->affected_rows();
				if ($uTypeSumAffectedRow < 1) {
					throw new RollbackException('회원 마일리지 유형 합계를 수정하면서 오류가 발생했습니다.');
				}
			}
		}
		
		// 마일리지 유효기간 초과에 대해 완료 처리한다. 
		$uExpirationP = [
			'is_expiration'=> 'Y',
			'charge_status'=> 4,
			'expiration_date'=> $today
		];

		$uExpirationQ = 'UPDATE `imi_mileage_charge` SET
								`is_expiration` = ?,
								`charge_status` = ?
							   WHERE `expiration_date` < ?
							   AND `charge_status` in (3,6)';

		$uExpirationResult = $db->execute($uExpirationQ, $uExpirationP);
		$uExpirationAffectedRow = $db->affected_rows();

		if ($uExpirationAffectedRow < 1) {
			throw new RollbackException('유효기간 초과에 대해 수정하면서 오류가 발생했습니다.');
		}

		$alertMessage = '유효기간 만료데이터를 정상적으로 삭제하였습니다.';

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
            echo $alertMessage.'<br>';
		}
    }