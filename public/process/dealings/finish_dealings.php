<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 상품권 거래 완료 (판매/구매)
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

    // Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/DealingsClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MileageClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/DealingsCommissionClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/SellItemClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/CouponClass.php';

	// Exception 파일 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화.
        $alertMessage = '';

		if ($connection === false) {
           throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$dealingsClass = new DealingsClass($db);
		$mileageClass = new MileageClass($db);
		$memberClass = new MemberClass($db);
		$commissionClass = new DealingsCommissionClass($db);
		$couponClass = new CouponClass($db);

		// return시 url 설정
		$returnUrl = SITE_ADMIN_DOMAIN.'/dealings_manage.php';

		// xss, injection 방지
		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$_GET['member_idx'] = htmlspecialchars($_GET['member_idx']);
		$_GET['is_cancel'] = htmlspecialchars($_GET['is_cancel']);
		$_GET['target'] = htmlspecialchars($_GET['target']);
		$getData = $_GET;

		$dealingsIdx = $getData['idx'];
		$isCancel = $getData['is_cancel']; //취소여부
		$mileageType = 7; // 마일리지타입은 '거래'
		$chargeStatus = 3; // 충전상태는 '충전' 

		$db->startTrans();

		// 환불 및 완료 시 필요한 데이터 추출 
		$chargeData = $dealingsClass->getMileageChargeDataByDealings($dealingsIdx);
		if ($chargeData === false) {
			throw new RollbackException('마일리지 충전에 필요한 데이터를 가져올 수 없습니다');
		}
		$dealingsMileage = $chargeData->fields['dealings_mileage']; // 거래금액
		$commission = $chargeData->fields['commission']; // 수수료

		$dealingsStatus = $chargeData->fields['dealings_status']; // 현재 거래상태
		$itemNo = $chargeData->fields['item_no']; //상품권종류
		$dealingsWriterIdx = $chargeData->fields['dealings_writer_idx'];
		$dealingsMemberIdx = $chargeData->fields['dealings_member_idx'];
	
		if ($getData['target'] == 'member_idx') {
			$couponUseParam = [
				'dealings_idx'=>$dealingsIdx,
				'member_idx'=>$dealingsWriterIdx,
				'issue_type'=>'구매',
				'is_refund'=>'N'
			];
		} else if ($getData['target'] == 'writer_idx') {
			$couponUseParam = [
				'dealings_idx'=>$dealingsIdx,
				'member_idx'=>$dealingsMemberIdx,
				'issue_type'=>'구매',
				'is_refund'=>'N'
			];
		}

		$useCouponData = $couponClass->getUseCouponData($couponUseParam);
		if ($useCouponData === false) {
			throw new RollbackException("쿠폰 사용 내역을 가져오면서 오류가 발생했습니다.");
		}
		$couponIdx = $useCouponData->fields['idx'];
		$coupon_use_mileage = $useCouponData->fields['coupon_use_mileage'];

		$commissionMemberIdx = 0;

		if ($isCancel == 'N') {
			if (!empty($couponIdx)){
				$dealingsMileage = $useCouponData->fields['coupon_use_before_mileage'];
			}
			
			// 수수료 정보 가져오기
			if ($getData['target'] == 'member_idx') {
				$commissionInfoParam = [
					'dealings_idx'=>$dealingsIdx,
					'member_idx'=>$dealingsMemberIdx,
					'issue_type'=>'판매',
					'is_refund'=>'N'
				];
				$commissionMemberIdx = $dealingsMemberIdx;
			} else if ($getData['target'] == 'writer_idx') {
				$commissionInfoParam = [
					'dealings_idx'=>$dealingsIdx,
					'member_idx'=>$dealingsWriterIdx,
					'issue_type'=>'판매',
					'is_refund'=>'N'
				];
				$commissionMemberIdx = $dealingsWriterIdx;
			}

			// 수수료 할인금액 가져오기
			$commissionData = $couponClass->getUseCouponData($commissionInfoParam);
			if ($commissionData === false) {
				throw new RollbackException("수수료 할인금액을 가져오면서 오류가 발생했습니다.");
			}

			$commisionCouponIdx = $commissionData->fields['idx'];

			if (!empty($commisionCouponIdx)) {
				// 할인쿠폰 사용
				$discountMoney = $commissionData->fields['discount_money'];
				$couponUseMileage = $commissionData->fields['coupon_use_mileage'];

				// 쿠폰 사용해서 100% 할인 받는 경우
				if ($couponUseMileage == 0) {
					$commission = $couponUseMileage;
				}

				$commission = ($commission - $couponUseMileage);
			}

			$dealingsMileage -= $commission; // 최종금액
		}
		
		// 쿠폰으로 결제한 경우 쿠폰 제외하고 결제한 금액만 돌려주어야 함
		if ($isCancel == 'Y') {

			if ($coupon_use_mileage > 0) {
				$dealingsMileage = $coupon_use_mileage;
			}

			if ($coupon_use_mileage == 0){
				$dealingsMileage = 0;
			}

			$nextStatus = 5;
			$alertMessage = '정상적으로 거래가 취소되었습니다.';
			
			if (!empty($couponIdx)){
				// 거래취소시 쿠폰복구 
				$couponStatusParam = [
					'coupon_use_end_date'=> date('Y-m-d'),
					'is_refund'=> 'Y',
					'idx'=>$couponIdx
				];

				$updateCouponResult = $couponClass->updateCouponStatus($couponStatusParam);
				if ($updateCouponResult < 1) {
					throw new RollbackException('거래취소로 쿠폰 복구 중에 문제가 생겼습니다.');
				}
			}
		} else {
			$statusData = [
				'dealings_status'=>$dealingsStatus
			];
			$nextStatus = $dealingsClass->getNextDealingsStatus($statusData);
			if ($nextStatus === false) {
				throw new RollbackException('거래 상태를 가져오는 중에 오류가 발생했습니다.');
			}
			$alertMessage = '정상적으로 거래가 완료되었습니다.';
		}

		
		if ($dealingsMileage > 0) {

			$chargeParam = [
				'idx'=>$getData['member_idx'],
				'account_bank'=>'아이엠아이',
				'account_no'=>setEncrypt($chargeData->fields['dealings_subject']),
				'charge_cost'=>$dealingsMileage,
				'spare_cost'=>$dealingsMileage,
				'charge_name'=>'관리자',
				'mileageType'=>$mileageType,
				'dealings_date'=>date('Y-m-d'),
				'charge_status'=>$chargeStatus
			];

			// 충전정보 추가
			$insertChargeResult = $mileageClass->insertMileageCharge($chargeParam);
			if ($insertChargeResult < 1) {
				throw new RollbackException('마일리지 충전 실패하였습니다.');
			}

			$mileageParam = [
				'charge_cost'=>$dealingsMileage,
				'idx'=>$getData['member_idx']
			];
			$updateResult = $memberClass->updateMileageCharge($mileageParam); // 마일리지변경
			if ($updateResult < 1) {
				throw new RollbackException('회원 마일리지 정보 수정 실패하였습니다.');
			}

			$memberMileageType = $mileageClass->getMemberMileageTypeIdx($getData['member_idx']);
			if ($memberMileageType == false) {
				$mileageTypeParam = [
					$getData['member_idx'], 
					$dealingsMileage
				];
				$mileageTypeInsert = $mileageClass->mileageTypeInsert($mileageType, $mileageTypeParam);
				if ($mileageTypeInsert < 1) {
					throw new RollbackException('마일리지 유형별 합계 삽입 실패 하였습니다.');
				} 
			} else {
				$mileageTypeParam = [
					$dealingsMileage,
					$getData['member_idx']
				];
				$mileageTypeUpdate = $mileageClass->mileageTypeChargeUpdate($mileageType, $mileageTypeParam);
				if ($mileageTypeUpdate < 1) {
					throw new RollbackException('마일리지 유형별 합계 정보 수정 실패 하였습니다.');
				}
			}

			$changeData = [
				'dealings_status'=>$nextStatus,
				'dealings_idx'=>$dealingsIdx
			];

			$mileagechangeIdx = $dealingsClass->getMileageChangeIdx($dealingsIdx);
			if ($mileagechangeIdx === false) {
				throw new RollbackException("거래 마일리지 변동정보를 읽어오다가 오류가 발생했습니다.");
			}

			// 마일리지 변동내역 상태 수정 (100% 쿠폰 할인인경우 결제 안했기 때문에 안해도 됨)		
			if(!empty($mileagechangeIdx)){
				$updateDealingsChangeResult = $dealingsClass->updateDealingsChange($changeData);
				if ($updateDealingsChangeResult < 1) {
					throw new RollbackException('거래 마일리지 내역에 상태 수정 실패하였습니다.');
				}
			}
		}

		// 거래 데이터 상태 수정
		$dealingsParam = [
			'dealings_status'=>$nextStatus,
			'idx'=>$dealingsIdx
		];

		$updateDealingsStatusResult = $dealingsClass->updateDealingsStatus($dealingsParam);
		if ($updateDealingsStatusResult < 1) {
			throw new RollbackException('거래정보 상태 수정 실패하였습니다.');
		}

		// 거래유저정보 상태 수정
		$userData = [
			'dealings_status'=>$nextStatus,
			'dealings_idx'=>$dealingsIdx
		];

		$updateDealingsUserResult = $dealingsClass->updateDealingsUser($userData);
		if ($updateDealingsUserResult < 1) {
			throw new RollbackException('거래 유저 수정 실패 하였습니다.');
		}

		// 처리절차 생성하기
		$processData = [
			'dealings_idx'=>$dealingsIdx,
			'dealings_status_idx'=>$nextStatus
		];

		$insertProcessResult = $dealingsClass->insertDealingsProcess($processData);
		if ($insertProcessResult < 1) {
			throw new RollbackException('거래 처리과정 생성 실패하였습니다.');
		}

		if ($isCancel == 'N') {
			if (!empty($couponIdx)){
				// 거래 완료시 쿠폰완료일자 표기 
				$couponStatusParam = [
					'coupon_use_end_date'=> date('Y-m-d'),
					'is_refund'=> 'N',
					'idx'=>$couponIdx
				];

				$updateCouponResult = $couponClass->updateCouponStatus($couponStatusParam);
				if ($updateCouponResult < 1) {
					throw new RollbackException('쿠폰 사용 완료 처리 하는 중에 오류가 발생했습니다.');
				}
			}

			// 수수료가 있는 경우에만 실행 (쿠폰을 사용해서 내지 않는 경우는 실행안함)
			if ($commission > 0) {
				// 수수료도 넣기
				$dealingsIdxFromDB = $commissionClass->isExistDealingsNo($dealingsIdx);
				if ($dealingsIdxFromDB === false){
					throw new RollbackException('수수료 테이블에서 거래키정보를 가져올 수 없습니다.');
				}

				if(!empty($dealingsIdxFromDB)){
					throw new RollbackException('이미 거래가 완료되었거나 환불처리 되었습니다.');
				}

				// 수수료 할인해주기
				// 쿠폰정보 업데이트 (날짜), 취소도 동일 취소는 환불까지 체크 할것 
				$commissionParam = [
					'dealings_idx'=>$dealingsIdx,
					'commission'=>$commission,
					'sell_item_idx'=>$itemNo
				];

				$insertCommissionResult = $commissionClass->insertDealingsCommission($commissionParam);
				if ($insertCommissionResult < 1) {
					throw new RollbackException('수수료 테이블에 삽입을 할 수 없습니다');
				}
			}
		}

		$db->commitTrans();
		$db->completeTrans();
	} catch (RollbackException $e) {
		// 트랜잭션 문제가 발생했을 때
		$alertMessage = $e->errorMessage();
		$db->rollbackTrans();
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