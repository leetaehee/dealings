<?php
	/**
	 * 마일리지 (충전, 취소, 출금 등)
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

		// injection, xss 방지코드
		$_POST['charge_cost'] = htmlspecialchars($_POST['charge_cost']);
		$postData = $_POST;

		$idx = $_SESSION['idx'];
		$mileageType = 5;

		// 유효성 검사 실패시 마일리지 타입별 링크
		if ($mileageType == 5) {
			$returnUrl = SITE_DOMAIN.'/virtual_account_mileage_withdrawal.php';
		}

		$mileageClass = new MileageClass($db);

		$resultMileageValidCheck = $mileageClass->checkChargeFormValidate($postData);
		if ($resultMileageValidCheck['isValid'] === false) {
			// 폼 데이터 받아서 유효성 검증
			throw new Exception($resultMileageValidCheck['errorMessage']);
		}

		// 트랜잭션시작
		$db->startTrans();

		// 출금가능 금액 추출
		$rTypeSumQ = 'SELECT `virtual_account_sum`
					  FROM `th_mileage_type_sum`
					  WHERE `member_idx` = ?
					  FOR UPDATE';
		
		$rTypeSumResult = $db->execute($rTypeSumQ, $idx);
		if ($rTypeSumResult === false) {
			throw new RollbackException('가상계좌 출금금액을 조회하면서 오류가 발생하였습니다.');
		}

		$maxMileage = $rTypeSumResult->fields['virtual_account_sum'];
		if ($postData['charge_cost'] > $maxMileage) {
			throw new RollbackException('가상계좌 출금금액을 초과 합니다.');
		}

		$rChargeListP = [
			'mileage_idx'=> $mileageType,
			'member_idx'=> $idx,
			'charge_status'=> 3
		];

		// 가상계좌 출금 가능 리스트 추출 
		$rChargeListQ = 'SELECT `idx`,
								`charge_cost`,
								`spare_cost`,
								`mileage_idx`
						 FROM `th_mileage_charge`
						 WHERE `mileage_idx` = ?
						 AND `member_idx` = ?
						 AND `charge_status` = ?
						 AND `spare_cost` > 0
						 ORDER BY `charge_date` ASC
						 FOR UPDATE';
	
		$rChargeListResult = $db->execute($rChargeListQ, $rChargeListP);
		if ($rChargeListResult == false) {
			throw new RollbackException('가상계좌 충전내역을 조회하면서 오류가 발생했습니다.');
		}

		$rAccountQ = 'SELECT `virtual_account_no`,
							 `bank_name`
					  FROM `th_member_virtual_account`
					  WHERE `member_idx` = ?';
			
		$rAccountResult = $db->execute($rAccountQ, $idx);
		if ($rAccountResult === false) {
			throw new RollbackException('가상계좌 정보를 조회하면서 오류가 발생했습니다.');
		}

		$accountNo = $rAccountResult->fields['virtual_account_no'];
		$accountBank = $rAccountResult->fields['bank_name'];

		// 마일리지 출금 처리
		$mileageP = [
			'mileageWithdrawalList'=> $rChargeListResult,
			'dealingsMileage'=> $postData['charge_cost'],
			'type'=> 'withdrawal',
			'member_idx'=> $idx,
			'account_no'=> $accountNo,
			'account_bank'=> $accountBank,
			'charge_name'=> $_SESSION['name'],
			'charge_status'=> 2
		];

		$payMileageResult = $mileageClass->withdrawalMileageProcess($mileageP);
		if ($payMileageResult['result'] === false) {
			throw new RollbackException($payMileageResult['resultMessage']);
		}

		$returnUrl = SITE_DOMAIN.'/my_withdrawal_list.php';
		$alertMessage = '출금이 완료되었습니다! 감사합니다';

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