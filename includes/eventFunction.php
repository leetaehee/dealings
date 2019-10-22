<?php
	/**
	 * 구매/판매 이벤트로서, 가입을 한 지 1달 미만인 회원에게 진행되는 이벤트
	 */

	$db->startTrans();

	if (empty($couponIdx)){
		// 구매 시, 페이백 하는 코드 (시작)
		$myInfomation = $memberClass->getMyInfomation($buyerMemberIdx, $isUseForUpdate);
		if ($myInfomation === false){
			throw rollbackException('내 정보를 가져오다가 오류가 발생했습니다.');
		}
		$joinApprovalDate = $myInfomation->fields['join_approval_date'];
		$joinAfterApprovalDate = date('Y-m-d', strtotime('+1 months',strtotime($joinApprovalDate)));

		if ($today < $joinAfterApprovalDate) { 
			$isJoinPeriodExceedPur = true; // 회원이 가입한지 1달이 안되었다면 구매 이벤트 적용
		}

		$isPurIngEvent = false;
		if ($isJoinPeriodExceedPur === true) {
			// 이벤트 기간 체크 (구매)
			$event = $CONFIG_EVENT_ARRAY['구매'];
			
			if ($event['start_date'] <= $today && $event['end_date'] >= $today) {
				$itemData = $sellItemClass->getSellItemData($itemNo, $isUseForUpdate);
				if ($itemData === false) {
					throw new RollbackException('구매 물품정보를 가져오면서 오류가 발생했습니다.');
				}

				// 이벤트 진행중이며, 페이백이 설정되어있는 경우만 이벤트 진행하도록 함.
				$payback = $itemData->fields['payback'];
				if ($payback > 0) {
					$isPurIngEvent = true;
				}
			} 
		}

		if ($isPurIngEvent === true) {
			// 이벤트 금액 계산
			$paybackDealings = round($fixedDealingsMileage*$payback)/100;

			// 페이백 충전하기 
			if ($paybackDealings > 0) {
				$paybackMileageType = 8;

				$paybackChargeParam = [
					'idx'=>$buyerMemberIdx,
					'account_bank'=> '아이엠아이',
					'account_no'=> setEncrypt($chargeData->fields['dealings_subject']." _페이백!!"),
					'charge_cost'=> $paybackDealings,
					'spare_cost'=> $paybackDealings,
					'charge_name'=> '관리자',
					'mileageType'=> $paybackMileageType,
					'dealings_date'=> date('Y-m-d'),
					'charge_status'=> $chargeStatus
				];

				// 충전정보 추가
				$insertPaybackChargeResult = $mileageClass->insertMileageCharge($paybackChargeParam);
				if ($insertPaybackChargeResult < 1) {
					throw new RollbackException('페이백으로 마일리지 충전 실패하였습니다.');
				}

				$paybackMileageParam = [
					'charge_cost'=>$paybackDealings,
					'idx'=>$buyerMemberIdx
				];
				$updatePaybackResult = $memberClass->updateMileageCharge($paybackMileageParam); // 마일리지변경
				if ($updatePaybackResult < 1) {
					throw new RollbackException('회원 마일리지 정보 수정 실패하였습니다.');
				}

				$memberPaybackMileageType = $mileageClass->getMemberMileageTypeIdx($buyerMemberIdx, $isUseForUpdate);
				if ($memberPaybackMileageType == false) {
					$payMileageTypeParam = [
						'buyerMemberIdx'=> $buyerMemberIdx, 
						'paybackDealings'=> $paybackDealings
					];
					$mileagePayTypeInsert = $mileageClass->mileageTypeInsert($paybackMileageType, $payMileageTypeParam);
					if ($mileagePayTypeInsert < 1) {
						throw new RollbackException('마일리지 유형별 합계 삽입 실패 하였습니다.');
					} 
				} else {
					$mileagePaybackTypeParam = [
						'paybackDealings'=> $paybackDealings,
						'buyerMemberIdx'=> $buyerMemberIdx
					];
					$mileagePayTypeUpdate = $mileageClass->mileageTypeChargeUpdate($paybackMileageType, $mileagePaybackTypeParam);
					if ($mileagePayTypeUpdate < 1) {
						throw new RollbackException('마일리지 유형별 합계 정보 수정 실패 하였습니다.');
					}
				}

				$eventBuyerHistoryParam = [
					'member_idx'=> $buyerMemberIdx,
					'event_type'=> '구매' 
				];

				$eventBuyerHistoryIdx = $eventClass->getIsExistEventHistoryIdx($eventBuyerHistoryParam, $isUseForUpdate);
				if ($eventBuyerHistoryIdx === false) {
					throw new RollbackException('구매 이벤트 히스토리 키를 가져오면서 오류가 발생했습니다.');
				}

				if (!empty($eventBuyerHistoryIdx)) {
					$historyParam = [
						'event_cost'=> $paybackDealings,
						'participate_count'=> 1,
						'eventHistoryIdx'=> $eventBuyerHistoryIdx
					];

					$updateEventSellerHistoryResult = $eventClass->updateEventHistory($historyParam);
					if ($updateEventSellerHistoryResult < 1) {
						throw new RollbackException('구매 환급률 히스토리를 수정하면서 오류가 발생했습니다.');
					}
				} else {
					// 데이터 추가
					$historyParam = [
						'member_idx'=> $buyerMemberIdx,
						'event_cost'=> $paybackDealings,
						'event_type'=> '구매',
						'participate_count'=> 1
					];

					$insertEventSellerHistoryResult = $eventClass->insertEventHistory($historyParam);
					if ($insertEventSellerHistoryResult < 1) {
						throw new RollbackException('구매 환급률 히스토리를 등록하면서 오류가 발생했습니다.');
					}
				}
			}
		}
		// 구매 시, 페이백 하는 코드 (종료)
	}

	if(!empty($commisionCouponIdx)){
		// 거래 완료시 쿠폰완료일자 표기 (판매자)
		$couponSellerStatusParam = [
			'coupon_use_end_date'=> date('Y-m-d'),
			'is_refund'=> 'N',
			'idx'=>$commisionCouponIdx
		];

		$updateSellerCouponResult = $couponClass->updateCouponStatus($couponSellerStatusParam);
		if ($updateSellerCouponResult < 1) {
			throw new RollbackException('쿠폰 사용 완료 처리 하는 중에 오류가 발생했습니다.');
		}
	}

	if(empty($commisionCouponIdx)){
		// 판매 시, 환급률 누적 하는 코드 (시작)
		$myInfomation = $memberClass->getMyInfomation($sellerMemberIdx, $isUseForUpdate);
		if ($myInfomation === false){
			throw rollbackException('내 정보를 가져오다가 오류가 발생했습니다.');
		}
		$joinApprovalDate = $myInfomation->fields['join_approval_date'];
		$joinAfterApprovalDate = date('Y-m-d', strtotime('+1 months',strtotime($joinApprovalDate)));

		if ($today < $joinAfterApprovalDate) { 
			$isJoinPeriodExceed = true; // 회원이 가입한지 1달이 안되었다면 구매 이벤트 적용
		}

		$isSellIngEvent = false;
		if ($isJoinPeriodExceed === true) {
			// 이벤트 기간 체크 (판매)
			$eventSell = $CONFIG_EVENT_ARRAY['판매'];
			
			if ($eventSell['start_date'] <= $today && $eventSell['end_date'] >= $today) {
				$isSellIngEvent = true;
			}
		}

		if ($isSellIngEvent === true) {
			$eventHistoryParam = [
				'member_idx'=> $sellerMemberIdx,
				'event_type'=> '판매' 
			];
			$eventHistoryIdx = $eventClass->getIsExistEventHistoryIdx($eventHistoryParam, $isUseForUpdate);
			if ($eventHistoryIdx === false) {
				throw new RollbackException('이벤트 히스토리 키를 가져오면서 오류가 발생했습니다.');
			}

			if (!empty($eventHistoryIdx)) {
				$historyParam = [
					'sellDealings'=> $commission,
					'participate_count'=> 1,
					'eventHistoryIdx'=> $eventHistoryIdx
				];

				$updateEventSellerHistoryResult = $eventClass->updateEventHistory($historyParam);
				if ($updateEventSellerHistoryResult < 1) {
					throw new RollbackException('판매 환급률 히스토리를 수정하면서 오류가 발생했습니다.');
				}
			} else {
				// 데이터 추가
				$historyParam = [
					'member_idx'=> $sellerMemberIdx,
					'event_cost'=> $commission,
					'event_type'=> '판매',
					'participate_count'=> 1
				];

				$insertEventSellerHistoryResult = $eventClass->insertEventHistory($historyParam);
				if ($insertEventSellerHistoryResult < 1) {
					throw new RollbackException('판매 환급률 히스토리를 등록하면서 오류가 발생했습니다.');
				}
			}
		}
		// 판매 시, 페이백 하는 코드 (종료)
	}

	$db->completeTrans();