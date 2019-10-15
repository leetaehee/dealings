<?php
	/**
	 * 마일리지 충전 (상품권)
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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php';

	// Exception 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
        $returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화.
        $alertMessage = '';
		$isUseForUpdate = true;

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$chargeDate = date('Y-m-d');

		// injection, xss 방지코드
		$_POST['account_bank'] = htmlspecialchars($_POST['account_bank']);
		$_POST['account_no'] = htmlspecialchars($_POST['account_no']);
		$_POST['charge_cost'] = htmlspecialchars($_POST['charge_cost']);
		$_POST['charge_name'] = htmlspecialchars($_POST['charge_name']);
		$postData = $_POST;
		
		$mileageType = 3;
		$idx = $_SESSION['idx'];

		$mileageClass = new MileageClass($db);
		$memberClass = new MemberClass($db);

		$resultMileageValidCheck = $mileageClass->checkChargeFormValidate($postData);

		// 유효성 검사 실패시 마일리지 리턴 URL
		$returnUrl = SITE_DOMAIN.'/virtual_account_charge.php'; 

		if($resultMileageValidCheck['isValid'] === false) {
			// 폼 데이터 받아서 유효성 검증
			throw new Exception($resultMileageValidCheck['errorMessage']);
		}

		$db->startTrans();

		$expirationData = $mileageClass->getExpirationDay($mileageType, $isUseForUpdate); //유효기간 만료일 구하기
        if ($expirationData === false) {
            throw new RollbackException('마일리지 만료정보 가져오는데 실패했습니다.');
		}

		$expirationDate = '';
		if ($expirationData['period'] != 'none') {
			// 유효기간 만료일자 지정
			$period = "+".$expirationData['day'].' '.$expirationData['period'];
			$expirationDate = date('Y-m-d',strtotime($period,strtotime($chargeDate)));
		}

		$chargeParam = [
			'idx'=>$idx,
			'account_bank'=> $postData['account_bank'],
			'account_no'=> setEncrypt($postData['account_no']),
			'charge_cost'=> $postData['charge_cost'],
			'spare_cost'=> $postData['charge_cost'],
			'charge_name'=> $postData['charge_name'],
			'mileageType'=> $mileageType,
			'chargeDate'=> $chargeDate,
			'charge_status'=> 3,
		];

		if (!empty($expirationDate)) {
			$chargeParam['expirationDate'] = $expirationDate;
		}

		$insertChargeResult = $mileageClass->insertMileageCharge($chargeParam); // 충전정보 추가
		if ($insertChargeResult < 1) {
			throw new RollbackException('마일리지 충전 실패했습니다.');
		}

		$mileageParam = [
			'charge_cost'=> $postData['charge_cost'],
			'idx'=> $idx
		];

		$updateResult = $memberClass->updateMileageCharge($mileageParam); // 마일리지변경
		if ($updateResult < 1) {
			throw new RollbackException('마일리지 충전 변경 실패했습니다.');
		}

		$memberMileageType = $mileageClass->getMemberMileageTypeIdx($idx);
		if ($memberMileageType == false) {
			$mileageTypeParam = [
				'idx'=> $idx, 
				'charge_cost'=> $postData['charge_cost']
			];

			$mileageTypeInsert = $mileageClass->mileageTypeInsert($mileageType, $mileageTypeParam);
			if ($mileageTypeInsert < 1) {
				throw new RollbackException('마일리지 유형별 합계 삽입 실패했습니다.');
			}
		} else {
			$mileageTypeParam = [
				'charge_cost'=> $postData['charge_cost'],
				'idx'=> $idx
			];

			$mileageTypeUpdate = $mileageClass->mileageTypeChargeUpdate($mileageType, $mileageTypeParam);
			if ($mileageTypeUpdate < 1) {
				throw new RollbackException('마일리지 유형별 합계 변동 실패했습니다.');
			}
		}

		$returnUrl = SITE_DOMAIN.'/my_charge_list.php';
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