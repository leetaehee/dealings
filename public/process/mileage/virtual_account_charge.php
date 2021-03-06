<?php
	/**
	 *  마일리지 충전 (가상계좌)
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
        $returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화.
        $alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// injection, xss 방지코드
		$_POST['account_bank'] = htmlspecialchars($_POST['account_bank']);
		$_POST['account_no'] = htmlspecialchars($_POST['account_no']);
		$_POST['charge_cost'] = htmlspecialchars($_POST['charge_cost']);
		$_POST['charge_name'] = htmlspecialchars($_POST['charge_name']);
		$postData = $_POST;
		
		$mileageType = 5;
		$idx = $_SESSION['idx'];

		$mileageClass = new MileageClass($db);

		$resultMileageValidCheck = $mileageClass->checkChargeFormValidate($postData);

		// 유효성 검사 실패시 마일리지 리턴 URL
		$returnUrl = SITE_DOMAIN.'/virtual_account_charge.php'; 

		if($resultMileageValidCheck['isValid'] === false) {
			// 폼 데이터 받아서 유효성 검증
			throw new Exception($resultMileageValidCheck['errorMessage']);
		}

		$db->startTrans();

		// 가상 계좌 체크하기
		$rAccountP = [
			'member_idx'=> $_SESSION['idx'],
			'account_bank'=> $postData['account_bank']
		];

		$rAccountQ = 'SELECT `virtual_account_no`
					  FROM `th_member_virtual_account`
					  WHERE `member_idx` = ?
					  AND `bank_name` = ?
					  FOR UPDATE';
			
		$rAccountResult = $db->execute($rAccountQ, $rAccountP);
		if ($rAccountResult === false) {
			throw new RollbackException('가상 계좌를 조회 하면서 오류가 발생했습니다.');
		}

		$myAccountNo = setDecrypt($rAccountResult->fields['virtual_account_no']);
		if ($postData['account_no'] != $myAccountNo) {
			throw new RollbackException('유효하지 않은 가상 계좌 입니다. 계좌를 확인하세요.');
		}

		$chargeParamGroup = [
			'charge_param' => [
				'member_idx'=> $idx,
				'charge_infomation'=> $postData['account_bank'],
				'chrage_account_no'=> setEncrypt($postData['account_no']),
				'charge_cost'=> $postData['charge_cost'],
				'spare_cost'=> $postData['charge_cost'],
				'charge_name'=> $postData['charge_name'],
				'mileage_idx'=> $mileageType,
				'charge_date'=> $today,
				'charge_status'=> 3
			],
			'mileageType'=> $mileageType
		];

		// 충전하기
		$chargeResult = $mileageClass->chargeMileageProcess($chargeParamGroup);
		if ($chargeResult['result'] === false) {
			throw new RollbackException($chargeResult['resultMessage']);
		}

		$returnUrl = SITE_DOMAIN . '/my_charge_list.php';
		$alertMessage = '마일리지가 충전되었습니다. 감사합니다.';

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