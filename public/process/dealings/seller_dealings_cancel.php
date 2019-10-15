<?php
	/**
	 * 상품권 거래 완료 (판매자시점)
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';

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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/EventClass.php';

	// Exception 파일 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화.
        $alertMessage = '';
		$today = date('Y-m-d');
		$isUseForUpdate = true;

		if ($connection === false) {
           throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$dealingsClass = new DealingsClass($db);
		$mileageClass = new MileageClass($db);
		$memberClass = new MemberClass($db);
		$commissionClass = new DealingsCommissionClass($db);
		$couponClass = new CouponClass($db);
		$sellItemClass = new SellItemClass($db);
		$eventClass = new EventClass($db);

		// return시 url 설정
		$returnUrl = SITE_DOMAIN . '/mypage.php';

		// xss, injection 방지
		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$_GET['target'] = htmlspecialchars($_GET['target']);
		$getData = $_GET;

		$dealingsIdx = $getData['idx'];
		
		$mileageType = 7; // 마일리지타입은 '거래'
		$chargeStatus = 3; // 충전상태는 '충전' 
		
		$db->startTrans();

		// 환불시 필요한 데이터 추출 
		$chargeData = $dealingsClass->getMileageChargeDataByDealings($dealingsIdx, $isUseForUpdate);
		if ($chargeData === false) {
			throw new RollbackException('마일리지 충전에 필요한 데이터를 가져올 수 없습니다');
		}
		$dealingsMileage = $chargeData->fields['dealings_mileage']; // 거래금액
		$fixedDealingsMileage = $dealingsMileage;
		$commission = $chargeData->fields['commission']; // 수수료

		$dealingsStatus = $chargeData->fields['dealings_status']; // 현재 거래상태
		$itemNo = $chargeData->fields['item_no']; //상품권종류
		$dealingsWriterIdx = $chargeData->fields['dealings_writer_idx']; // 거래글 작성자
		$dealingsMemberIdx = $chargeData->fields['dealings_member_idx']; // 거래하는 사람
		$sellerMemberIdx = 0;

		if ($getData['target'] == 'member_idx') {
			$couponUseParam = [
				'dealings_idx'=> $dealingsIdx,
				'member_idx'=> $dealingsWriterIdx,
				'issue_type'=> '구매',
				'is_refund'=> 'N'
			];
			$sellerMemberIdx = $dealingsMemberIdx;
			$buyerMemberIdx = $dealingsWriterIdx;
		} else if ($getData['target'] == 'writer_idx') {
			$couponUseParam = [
				'dealings_idx'=> $dealingsIdx,
				'member_idx'=> $dealingsMemberIdx,
				'issue_type'=> '구매',
				'is_refund'=> 'N'
			];
			$sellerMemberIdx = $dealingsWriterIdx;
			$buyerMemberIdx = $dealingsMemberIdx;
		}

		$useCouponData = $couponClass->getUseCouponData($couponUseParam, $isUseForUpdate);
		if ($useCouponData === false) {
			throw new RollbackException("쿠폰 사용 내역을 가져오면서 오류가 발생했습니다.");
		}
		$couponIdx = $useCouponData->fields['idx'];
		$coupon_use_mileage = $useCouponData->fields['coupon_use_mileage'];
		$couponMemberIdx = $useCouponData->fields['coupon_member_idx'];

		$commissionMemberIdx = 0;

		$nextStatus = 5;

		if (!empty($couponIdx)){
			if ($coupon_use_mileage > 0) {
				$dealingsMileage = $coupon_use_mileage;
			}

			if ($coupon_use_mileage == 0){
				$dealingsMileage = 0;
			}

			// 거래취소시 쿠폰 환불정보 수정 (구매자)
			$couponStatusParam = [
				'coupon_use_end_date'=> date('Y-m-d'),
				'is_refund'=> 'Y',
				'idx'=>$couponIdx
			];

			$updateCouponResult = $couponClass->updateCouponStatus($couponStatusParam);
			if ($updateCouponResult < 1) {
				throw new RollbackException('구매자 쿠폰: 거래취소로 쿠폰 복구 중에 문제가 생겼습니다.');
			}

			// 거래 취소시 구매자 쿠폰 복구
			$couponStatusName = '사용대기';

			$couponStatusCode = $couponClass->getCouponStatusCode($couponStatusName, $isUseForUpdate);
			if ($couponStatusCode === false) {
				throw new RollbackException('쿠폰 상태 코드를 가져오면서 오류가 발생했습니다.');
			}

			if (empty($couponStatusCode)) {
				throw new RollbackException('쿠폰 상태 코드를 찾을 수 없습니다.');
			}

			$couponPurMbStParam = [
				'coupon_status'=> $couponStatusCode,
				'idx'=> $couponMemberIdx
			];

			$updateCouponPurMbStResult = $couponClass->updateCouponMemberStatus($couponPurMbStParam);
			if ($updateCouponPurMbStResult < 1) {
				throw new RollbackException('쿠폰 상태 코드를 변경하면서 오류가 발생했습니다.');
			}
		}

		$couponSellUseParam = [
			'dealings_idx'=>$dealingsIdx,
			'member_idx'=>$sellerMemberIdx,
			'issue_type'=>'판매',
			'is_refund'=>'N'
		];


		$useSellCouponData = $couponClass->getUseCouponData($couponSellUseParam, $isUseForUpdate);
		if ($useSellCouponData === false) {
			throw new RollbackException("쿠폰 사용 내역을 가져오면서 오류가 발생했습니다.");
		}
		$couponSellerIdx = $useSellCouponData->fields['idx'];
		$couponSellerMemberIdx = $useSellCouponData->fields['coupon_member_idx'];

		if (!empty($couponSellerIdx)) {

			// 거래취소시 쿠폰 환불정보 수정 (판매자)
			$couponSellStatusParam = [
				'coupon_use_end_date'=> date('Y-m-d'),
				'is_refund'=> 'Y',
				'idx'=>$couponSellerIdx
			];

			$updateSellerCouponResult = $couponClass->updateCouponStatus($couponSellStatusParam);
			if ($updateSellerCouponResult < 1) {
				throw new RollbackException('판매자 쿠폰: 거래취소로 쿠폰 복구 중에 문제가 생겼습니다.');
			}

			// 거래 취소시 판매자 쿠폰 복구
			$couponStatusName = '사용대기';

			$couponStatusCode = $couponClass->getCouponStatusCode($couponStatusName, $isUseForUpdate);
			if ($couponStatusCode === false) {
				throw new RollbackException('쿠폰 상태 코드를 가져오면서 오류가 발생했습니다.');
			}

			if (empty($couponStatusCode)) {
				throw new RollbackException('쿠폰 상태 코드를 찾을 수 없습니다.');
			}

			$couponSellMbStParam = [
				'coupon_status'=> $couponStatusCode,
				'idx'=> $couponMemberIdx
			];

			$updateCouponSellMbStResult = $couponClass->updateCouponMemberStatus($couponSellMbStParam);
			if ($updateCouponSellMbStResult < 1) {
				throw new RollbackException('쿠폰 상태 코드를 변경하면서 오류가 발생했습니다.');
			}
		}

		if ($dealingsMileage > 0) {
			$chargeParam = [
				'idx'=> $buyerMemberIdx,
				'account_bank'=> '아이엠아이',
				'account_no'=> setEncrypt($chargeData->fields['dealings_subject']),
				'charge_cost'=> $dealingsMileage,
				'spare_cost'=> $dealingsMileage,
				'charge_name'=> '관리자',
				'mileageType'=> $mileageType,
				'dealings_date'=> date('Y-m-d'),
				'charge_status'=> $chargeStatus
			];

			// 충전정보 추가
			$insertChargeResult = $mileageClass->insertMileageCharge($chargeParam);
			if ($insertChargeResult < 1) {
				throw new RollbackException('마일리지 충전 실패하였습니다.');
			}

			$mileageParam = [
				'charge_cost'=>$dealingsMileage,
				'idx'=> $buyerMemberIdx
			];
			$updateResult = $memberClass->updateMileageCharge($mileageParam); // 마일리지변경
			if ($updateResult < 1) {
				throw new RollbackException('회원 마일리지 정보 수정 실패하였습니다.');
			}

			$memberMileageType = $mileageClass->getMemberMileageTypeIdx($buyerMemberIdx, $isUseForUpdate);
			if ($memberMileageType == false) {
				$mileageTypeParam = [
					'member_idx'=> $buyerMemberIdx, 
					'mileage'=> $dealingsMileage
				];
				$mileageTypeInsert = $mileageClass->mileageTypeInsert($mileageType, $mileageTypeParam);
				if ($mileageTypeInsert < 1) {
					throw new RollbackException('마일리지 유형별 합계 삽입 실패 하였습니다.');
				} 
			} else {
				$mileageTypeParam = [
					'mileage'=> $dealingsMileage,
					'member_idx'=> $buyerMemberIdx
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

			$mileagechangeIdx = $dealingsClass->getMileageChangeIdx($dealingsIdx, $isUseForUpdate);
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

		$alertMessage = '정상적으로 거래가 취소되었습니다.';
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