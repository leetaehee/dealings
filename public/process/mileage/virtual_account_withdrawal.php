<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 마일리지 (충전, 취소, 출금 등)
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	// Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MileageClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/VirtualAccountClass.php';


	// Exception 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

    try {
		$processDate = date('Y-m-d');
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
		$memberClass = new MemberClass($db);
		$virtualClass = new VirtualAccountClass($db);

		$resultMileageValidCheck = $mileageClass->checkChargeFormValidate($postData);
		if( $resultMileageValidCheck['isValid'] === false) {
			// 폼 데이터 받아서 유효성 검증
			throw new Exception($resultMileageValidCheck['errorMessage']);
		}

		// 트랜잭션시작
		$db->startTrans();

		// 마일리지 초과하는지 확인
		$maxMileageParam = [
			'mileageType'=>$mileageType,
			'idx'=>$idx
		];

		$maxMileage = $mileageClass->getAvailableMileage($maxMileageParam);
		if ($maxMileage === false) {
			throw new RollbackException('출금 가능한 마일리지 가져오는 중에 오류 발생! 관리자에게 문의하세요!');
		}

		if ($postData['charge_cost'] > $maxMileage) {
			throw new RollbackException('출금금액이 출금가능금액을 초과합니다!');
		}

		$param = [
			'mileage_idx'=>$mileageType,
			'member_idx'=>$idx,
			'charge_status'=>3
		];

		// 충전가능한 내역 리스트
		$virtualWitdrawaLlist = $mileageClass->getVirutalAccountWithdrawalPossibleList($param);
		if ($virtualWitdrawaLlist === false) {
			throw new RollbackException('충전 내역을 가져오는 중에 오류가 발생하였습니다.');
		}
		
		// 회원 가상 계좌 가져오기
		$accountData = $virtualClass->getVirtualAccountData($idx);
		if ($accountData === false) {
			throw new RollbackException('가상계좌를 가져오는 중에 오류가 발생했습니다.');
		}

		$accountNo = $accountData->fields['virtual_account_no'];
		$accountBank = $accountData->fields['bank_name'];

		//imi_mileage_charge 수량 변경을 위한 정보 얻어오기
		$pChargeData = $postData['charge_cost']; 
		$chargeArray = $mileageClass->getMildateChargeInfomationData($virtualWitdrawaLlist, $pChargeData);
		if ($chargeArray === false) {
			throw new RollbackException('수량정보 데이터를 가져오는 중에 오류가 발생하였습니다.');
		}
		$chargeData = $chargeArray['update_data'];

		$updateChargeResult = $mileageClass->updateMileageCharge($chargeData);
		if ($updateChargeResult === false) {
			throw new RollbackException('출금 수정 실패! 관리자에게 문의하세요');
		}

		$spareZeroCount = $mileageClass->getCountChargeSpareCountZero();
		if ($spareZeroCount < 0) {
			throw new RollbackException('마일리지 상태 조회 오류! 관리자에게 문의하세요');
		}

		if ($spareZeroCount > 0) {
			$updateZeroResult = $mileageClass->updateChargeZeroStatus();
			if ($updateZeroResult === false) {
				throw new RollbackException('마일리지 출금 상태 변경 실패! 관리자에게 문의하세요');
			}
		}

		$mileageChangeParam = [
			'memberIdx'=>$idx,
			'mileageIdx'=>$mileageType,
			'accountNo'=>$accountNo,
			'accountBank'=>$accountBank,
			'chargeName'=>$_SESSION['name'],
			'chargeStatus'=>2,
			'process_date'=>date('Y-m-d')
		];
		$changeData = $mileageClass->updateMileageArray($chargeData, $mileageChangeParam);
		if ($changeData === false) {
			throw new RollbackException('배열 데이터 생성 오류');
		}

		// 출금데이터 생성
		$insertChangeResult = $mileageClass->insertMileageChange($changeData);
		if ($insertChangeResult < 1) {
			throw new RollbackException('출금데이터 생성 실패! 관리자에게 문의하세요');
		}

		$mileageParam = [
			'mileage'=>$postData['charge_cost'],
			'idx'=>$idx
		];

		$updateResult = $memberClass->updateMileageWithdrawal($mileageParam); // 마일리지변경
		if ($updateResult < 1) {
			throw new RollbackException('회원 마일리지 수정 실패! 관리자에게 문의하세요');
		}

		$mileageTypeParam = [
			$postData['charge_cost'], 
			$idx
		];
		$mileageTypeUpdate = $mileageClass->mileageTypeWithdrawalUpdate($mileageType, $mileageTypeParam);
		if ($mileageTypeUpdate < 1) {
			throw new RollbackException('마일리지 유형별 출금금액 수정 오류가 발생했습니다.');
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