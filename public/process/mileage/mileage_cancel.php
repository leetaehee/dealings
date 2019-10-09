<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 마일리지 취소
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
        $alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$returnUrl = SITE_ADMIN_DOMAIN.'/charge_list.php'; // 작업이 끝나고 이동하는 URL
		
		// xss, inject 방지 코드
		$chargeIdx = isset($_GET['idx']) ? htmlspecialchars($_GET['idx']) : '';
		if (empty($chargeIdx)) {
			throw new Exception('잘못된 접근입니다! 관리자에게 문의하세요.');
		}

		$mileageClass = new MileageClass($db);
		$memberClass = new MemberClass($db);

		$db->startTrans();

		$chargeData = $mileageClass->getChargeInsertData($chargeIdx);
		if ($chargeData === false) {
			throw new RollbackException('충전정보를 가져오는 중에 오류가 발생했습니다.');
		}

		// 충전취소는 남은금액은 삭감시키지않는다.
		$statusParam = [
			'charge_status'=> 1,
			'spare_cost'=> 0,
			'use_cost'=> 0,
			'idx'=> $chargeIdx
		];
		$updateStatusResult = $mileageClass->updateChargeStatus($statusParam);
		if ($updateStatusResult < 1) {
			throw new RollbackException('충전내역 상태를 변경하는 중에 오류가 발생했습니다.');
		}

		$mileageChangeParam[] = [
				'member_idx'=>$chargeData->fields['member_idx'],
				'mileage_idx'=>$chargeData->fields['mileage_idx'],
				'accountNo'=>$chargeData->fields['charge_account_no'],
				'accountBank'=>$chargeData->fields['charge_infomation'],
				'chargeName'=>$chargeData->fields['charge_name'],
				'chargeStatus'=>1,
				'process_date'=>date('Y-m-d'),
				'charge_idx'=>$chargeData->fields['idx'],
				'charge_cost'=>$chargeData->fields['charge_cost'],
			];
		$mileageType = $chargeData->fields['mileage_idx'];

		// 출금데이터 생성
		$insertChangeResult = $mileageClass->insertMileageChange($mileageChangeParam);
		if ($insertChangeResult < 1) {
			throw new RollbackException('출금데이터 생성 실패했습니다.');
		}

		$mileageParam = [
			'charge_cost'=>$chargeData->fields['charge_cost'],
			'idx'=>$chargeData->fields['member_idx']
		];

		$updateResult = $memberClass->updateMileageWithdrawal($mileageParam); // 마일리지변경
		if ($updateResult < 1) {
			throw new RollbackException('회원 마일리지 수정 실패 실패했습니다.');
		}

		$mileageTypeParam = [
			'charge_cost'=>$chargeData->fields['charge_cost'],
			'idx'=>$chargeData->fields['member_idx']
		];

		$mileageTypeUpdate = $mileageClass->mileageTypeWithdrawalUpdate($mileageType, $mileageTypeParam);
		if ($mileageTypeUpdate < 1) {
			throw new RollbackException('마일리지 유형별 출금 합계 수정 오류가 발생했습니다.');
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