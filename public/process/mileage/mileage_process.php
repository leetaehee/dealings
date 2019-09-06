<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 마일리지 (충전, 취소, 출금 등)
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/mailer.lib.php'; // PHP메일보내기

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php'; // adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php'; // adodb

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MileageClass.php'; // Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php'; // Class 파일

	$returnUrl = ''; // 리턴되는 화면 URL 초기화.
	$isValid = true;
	$mode = isset($_POST['mode']) ?  $_POST['mode'] : $_GET['mode'];
	
	/*
	 * 마일리지 구분- 가상계좌(5), 문화상품권(3), 휴대전화(2), 신용카드(1)
	 */

	if ($mode == 'charge') {
		/*
		 * 가상계좌 충전
		 */
		$chargeDate = date('Y-m-d');

		$postData = $_POST;
		$mileageType = $postData['mileage_type'];

		// 유효성 검사 실패시 마일리지 타입별 링크
		if ($mileageType == 5) {
			$returnUrl = SITE_DOMAIN.'/virtual_account_charge.php'; 
		} else if ($mileageType == 1) {
			$returnUrl = SITE_DOMAIN.'/card_charge.php'; 
		} else if ($mileageType == 3) {
			$returnUrl = SITE_DOMAIN.'/voucher_charge.php'; 
		} else if ($mileageType == 2) {
			$returnUrl = SITE_DOMAIN.'/phone_charge.php'; 
		}

		$mileageClass = new MileageClass($db);
		$memberClass = new MemberClass($db);
		$resultMileageValidCheck = $mileageClass->checkChargeFormValidate($postData);

		if($resultMileageValidCheck['isValid']==false){
			// 폼 데이터 받아서 유효성 검증
			alertMsg($returnUrl, 1, $resultMileageValidCheck['errorMessage']);
		}

		// 트랜잭션 처리 할것!
		$db->beginTrans();

		$expirationData = $mileageClass->getExpirationDay($mileageType); //유효기간 만료일 구하기
		if ($expirationData == false) {
			$db->rollbackTrans();
			alertMsg($returnUrl, 1, '오류입니다! 관리자에게 문의하세요!');
		} else {
			$expirationDate = '';
			if ($expirationData['period'] != 'none') {
				// 유효기간 만료일자 지정
				$period = "+".$expirationData['day'].' '.$expirationData['period'];
				$expirationDate = date('Y-m-d',strtotime($period,strtotime($chargeDate)));
			}

			$chargeParam = [
					'idx'=>$postData['idx'],
					'account_bank'=>$postData['account_bank'],
					'account_no'=>setEncrypt($postData['account_no']),
					'charge_cost'=>$postData['charge_cost'],
					'spare_cost'=>$postData['charge_cost'],
					'charge_name'=>$postData['charge_name'],
					'mileageType'=>$mileageType,
					'chargeDate'=>$chargeDate,
					'charge_status'=>3,
				];
			
			if(!empty($expirationDate)){
				$chargeParam['expirationDate'] = $expirationDate;
			}
			
			$insertChargeResult = $mileageClass->insertMileageCharge($chargeParam); // 충전정보 추가

			if($insertChargeResult < 1){
				$db->rollbackTrans();
				alertMsg($returnUrl, 1, '`imi_mileage_charge ` 테이블 오류! 관리자에게 문의하세요!');
			}

			$mileageParam = [
					'charge_cost'=>$postData['charge_cost'],
					'idx'=>$postData['idx']
				];

			$updateResult = $memberClass->updateMileageCharge($mileageParam); // 마일리지변경

			if ($updateResult < 1) {
				$db->rollbackTrans();
				alertMsg($returnUrl, 1, '`imi_members` 테이블 오류! 관리자에게 문의하세요!');
			}

			$returnUrl = SITE_DOMAIN.'/mypage.php';

			$db->commitTrans();

			alertMsg($returnUrl, 1, '충전이 완료되었습니다! 감사합니다');
		}
	} else if ($mode == 'withdrawal') {
		/*
		 * 가상계좌 출금
		 */
		$processDate = date('Y-m-d');

		$postData = $_POST;
		

		// 유효성 검사 실패시 마일리지 타입별 링크
		if ($postData['mileageType'] == 5) {
			$returnUrl = SITE_DOMAIN.'/virtual_account_mileage_withdrawal.php';
		}

		$mileageClass = new MileageClass($db);
		$memberClass = new MemberClass($db);
		$resultMileageValidCheck = $mileageClass->checkChargeFormValidate($postData);

		if($resultMileageValidCheck['isValid']==false){
			// 폼 데이터 받아서 유효성 검증
			alertMsg($returnUrl, 1, $resultMileageValidCheck['errorMessage']);
		}

		/*
		 * 출금데이터 하기전에 select를 통해 출금가능데이터가 변경되면 안되도록
		 * 트랜잭션 처리가 필요하나, 현재는 db 구조로 인해 진행하지 않음
		 */

		$mileageChangeParam = [
				'memberIdx'=>$postData['idx'],
				'mileageIdx'=>$postData['mileageType'],
				'chargeCost'=>$postData['charge_cost'],
				'accountNo'=>$postData['account_no'],
				'accountBank'=>$postData['account_bank'],
				'chargeName'=>$postData['charge_name'],
				'chargeStatus'=>2
			];
		
		$db->beginTrans(); // 트랜잭션시작
		
		// 출금데이터 생성
		$insertChangeResult = $mileageClass->insertMileageChange($mileageChangeParam);
		
		if ($insertChangeResult < 1) {
			$db->rollbackTrans();
			alertMsg($returnUrl, 1, '`imi_mileage_change` 테이블 오류! 관리자에게 문의하세요!');
		}

		$mileageParam = [
				'charge_cost'=>$postData['charge_cost'],
				'idx'=>$postData['idx']
			];

		$updateResult = $memberClass->updateMileageWithdrawal($mileageParam); // 마일리지변경

		if ($updateResult < 1) {
			$db->rollbackTrans();
			alertMsg($returnUrl, 1, '`imi_members` 테이블 오류! 관리자에게 문의하세요!');
		}

		$returnUrl = SITE_DOMAIN.'/mypage.php';
		$db->commitTrans();

		alertMsg($returnUrl, 1, '출금이 완료되었습니다! 감사합니다');
	}