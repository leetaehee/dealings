<?php
	/**
	 *  마일리지 취소
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
        $alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$returnUrl = SITE_ADMIN_DOMAIN . '/charge_list.php'; // 작업이 끝나고 이동하는 URL
		
		// xss, inject 방지 코드
		$chargeIdx = isset($_GET['idx']) ? htmlspecialchars($_GET['idx']) : '';
		
		if (empty($chargeIdx)) {
			throw new Exception('잘못된 접근입니다! 관리자에게 문의하세요.');
		}

		$mileageClass = new MileageClass($db);

		$db->startTrans();

		$rChargeQ = 'SELECT `member_idx`,
							`mileage_idx`,
							`charge_account_no` ,
							`charge_infomation`,
							`charge_name`,
							`charge_status`,
							`idx`,
							`charge_cost`
					 FROM `imi_mileage_charge`
					 WHERE `idx` = ?
					 FOR UPDATE';
			
		$rChargeResult = $db->execute($rChargeQ, $chargeIdx);
		if ($rChargeResult === false) {
			throw new RollbackException('충전정보를 조회 하면서 오류가 발생했습니다.');
		}

		$chargeStatus = $rChargeResult->fields['charge_status'];
		if ($chargeStatus == 1) {
			throw new RollbackException('이미 충전 취소 되었습니다.');
		}

		$uChargeQ = 'UPDATE `imi_mileage_charge` SET
					   `charge_status` = 1,
					   `spare_cost` = 0,
					   `use_cost` = 0
					  WHERE `idx` = ?';
			
		$uChargeResult = $db->execute($uChargeQ, $chargeIdx);
		
		$chargeAffectedRow = $db->affected_rows();
		if ($chargeAffectedRow < 1) {
			throw new RollbackException('충전 취소 하면서 오류가 발생했습니다.');
		}

		// 마일리지 타입 
		$mileageType = $rChargeResult->fields['mileage_idx'];

		// 마일리지 변동내역에 추가
		$cChargeChangeP = [
			'member_idx'=> $rChargeResult->fields['member_idx'],
			'mileage_idx'=> $rChargeResult->fields['mileage_idx'],
			'charge_account_no'=> $rChargeResult->fields['charge_account_no'],
			'charge_infomation'=> $rChargeResult->fields['charge_infomation'],
			'charge_name'=> $rChargeResult->fields['charge_name'],
			'charge_idx'=> $rChargeResult->fields['idx'],
			'charge_cost'=> $rChargeResult->fields['charge_cost'],
			'charge_status'=> 1
		];

		$cChargeChangeQ = 'INSERT INTO `imi_mileage_change` SET 
							`member_idx` = ?,
							`mileage_idx` = ?,
							`charge_account_no` = ?,
							`charge_infomation` = ?,
							`charge_name` = ?,
							`charge_idx` = ?,
							`charge_cost` = ?,
							`charge_status` = ?,
							`process_date` = CURDATE()';

		$cChargeChangeResult = $db->execute($cChargeChangeQ, $cChargeChangeP);
		
		$chargeInsertId = $db->insert_id(); 
		if ($chargeInsertId < 1) {
			throw new RollbackException('마일리지 변동내역에 추가하면서 오류가 발생했습니다.');
		}

		// 회원 마일리지 변경
		$uMileageP = [
			'charge_cost'=> $rChargeResult->fields['charge_cost'],
			'idx'=> $rChargeResult->fields['member_idx']
		];

		$uMileageQ = 'UPDATE `imi_members` SET
					   `mileage` = `mileage` - ? 
					  WHERE `idx` = ?';

		$uMileageResult = $db->execute($uMileageQ, $uMileageP);
		
		$mileageAffectedRow = $db->affected_rows();
		if ($mileageAffectedRow < 1) {
			throw new RollbackException('회원 마일리지를 수정하면서 오류가 발생했습니다.');
		}

		// 마일리지 타입 컬럼명 추출
		$colName = $CONFIG_MILEAGE_TYPE_COLUMN[$mileageType];

		$uMileageSumP = [
			'charge_cost'=> $rChargeResult->fields['charge_cost'],
			'idx'=> $rChargeResult->fields['member_idx']
		];

		$uMileageSumQ = "UPDATE `imi_mileage_type_sum` SET 
						  `{$colName}` = `{$colName}` - ?
						 WHERE `member_idx` = ?";

		$uMileageSumResult = $db->execute($uMileageSumQ, $uMileageSumP);
		
		$mileageSumAffectedRow = $db->affected_rows();
		if ($mileageSumAffectedRow < 1) {
			throw new RollbackException('마일리지 유형별 합계 수정하면서 오류가 발생했습니다.');
		}

		$alertMessage = '충전이 취소 되었습니다.';

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
            alertMsg(SITE_DOMAIN, 0);
        }
    }