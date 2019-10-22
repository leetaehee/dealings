<?php
	/**
	 * 상품권 거래 완료 (판매자 시점)
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

		// 완료 시 필요한 데이터 추출 
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
		$commissionData = $couponClass->getUseCouponData($commissionInfoParam, $isUseForUpdate);
		if ($commissionData === false) {
			throw new RollbackException("수수료 할인금액을 가져오면서 오류가 발생했습니다.");
		}

		$commisionCouponIdx = $commissionData->fields['idx'];
		$discountRate = $commissionData->fields['discount_rate'];

		if (!empty($commisionCouponIdx)) {
			// 할인쿠폰 사용
			$discountMoney = $commissionData->fields['discount_money'];
			$couponUseMileage = $commissionData->fields['coupon_use_mileage'];

			// 쿠폰 사용해서 100% 할인 받는 경우
			$commission -= $couponUseMileage;
		}
		$dealingsMileage -= $commission; // 최종금액

		$statusData = [
			'dealings_status'=> $dealingsStatus
		];
		$nextStatus = $dealingsClass->getNextDealingsStatus($statusData, $isUseForUpdate);
		if ($nextStatus === false) {
			throw new RollbackException('거래 상태를 가져오는 중에 오류가 발생했습니다.');
		}

		if ($dealingsMileage > 0) {
			$chargeParam = [
				'idx'=> $sellerMemberIdx,
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
				'charge_cost'=> $dealingsMileage,
				'idx'=> $sellerMemberIdx
			];
			$updateResult = $memberClass->updateMileageCharge($mileageParam); // 마일리지변경
			if ($updateResult < 1) {
				throw new RollbackException('회원 마일리지 정보 수정 실패하였습니다.');
			}

			$memberMileageType = $mileageClass->getMemberMileageTypeIdx($sellerMemberIdx, $isUseForUpdate);
			if ($memberMileageType == false) {
				$mileageTypeParam = [
					'member_idx'=> $sellerMemberIdx, 
					'mileage'=> $dealingsMileage
				];
				$mileageTypeInsert = $mileageClass->mileageTypeInsert($mileageType, $mileageTypeParam);
				if ($mileageTypeInsert < 1) {
					throw new RollbackException('마일리지 유형별 합계 삽입 실패 하였습니다.');
				} 
			} else {
				$mileageTypeParam = [
					'mileage'=> $dealingsMileage,
					'member_idx'=> $sellerMemberIdx
				];
				$mileageTypeUpdate = $mileageClass->mileageTypeChargeUpdate($mileageType, $mileageTypeParam);
				if ($mileageTypeUpdate < 1) {
					throw new RollbackException('마일리지 유형별 합계 정보 수정 실패 하였습니다.');
				}
			}

			$changeData = [
				'dealings_status'=> $nextStatus,
				'dealings_idx'=> $dealingsIdx
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
			'dealings_status'=> $nextStatus,
			'idx'=> $dealingsIdx
		];

		$updateDealingsStatusResult = $dealingsClass->updateDealingsStatus($dealingsParam);
		if ($updateDealingsStatusResult < 1) {
			throw new RollbackException('거래정보 상태 수정 실패하였습니다.');
		}

		// 거래유저정보 상태 수정
		$userData = [
			'dealings_status'=> $nextStatus,
			'dealings_idx'=> $dealingsIdx
		];

		$updateDealingsUserResult = $dealingsClass->updateDealingsUser($userData);
		if ($updateDealingsUserResult < 1) {
			throw new RollbackException('거래 유저 수정 실패 하였습니다.');
		}

		// 처리절차 생성하기
		$processData = [
			'dealings_idx'=> $dealingsIdx,
			'dealings_status_idx'=> $nextStatus
		];

		$insertProcessResult = $dealingsClass->insertDealingsProcess($processData);
		if ($insertProcessResult < 1) {
			throw new RollbackException('거래 처리과정 생성 실패하였습니다.');
		}

		// 수수료가 있는 경우에만 실행 (쿠폰을 사용해서 내지 않는 경우는 실행안함)
		if ($commission > 0) {
			// 수수료도 넣기
			$dealingsIdxFromDB = $commissionClass->isExistDealingsNo($dealingsIdx, $isUseForUpdate);
			if ($dealingsIdxFromDB === false){
				throw new RollbackException('수수료 테이블에서 거래키정보를 가져올 수 없습니다.');
			}

			if(!empty($dealingsIdxFromDB)){
				throw new RollbackException('이미 거래가 완료되었거나 환불처리 되었습니다.');
			}

			// 수수료 할인해주기
			// 쿠폰정보 업데이트 (날짜), 취소도 동일 취소는 환불까지 체크 할것 
			$commissionParam = [
				'dealings_idx'=> $dealingsIdx,
				'commission'=> $commission,
				'sell_item_idx'=> $itemNo
			];

			$insertCommissionResult = $commissionClass->insertDealingsCommission($commissionParam);
			if ($insertCommissionResult < 1) {
				throw new RollbackException('수수료 테이블에 삽입을 할 수 없습니다');
			}
		}

		$returnUrl = SITE_DOMAIN . '/my_sell_list.php';
		$alertMessage = '정상적으로 거래가 완료되었습니다.';

		$db->completeTrans();

		// 이벤트 
		include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/eventFunction.php';

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