<?php
	/**
	 * 상품권 거래 완료 (판매자 시점)
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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/SellItemClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/CouponClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/EventClass.php';

	// Exception 파일 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화.
        $alertMessage = '';
		$today = date('Y-m-d');

		if ($connection === false) {
           throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$dealingsClass = new DealingsClass($db);
		$mileageClass = new MileageClass($db);
		$couponClass = new CouponClass($db);
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

		$rDealingsQ = 'SELECT `dealings_mileage`,
							  `dealings_commission`,
							  ROUND((`dealings_mileage` * `dealings_commission`)/100) `commission`,
							  `dealings_subject`,
							  `item_no`,
							  `idx`
						FROM `th_dealings`
						WHERE `idx` = ?
						FOR UPDATE';
		
		// 거래 테이블 조회
		$dealingsResult = $db->execute($rDealingsQ, $dealingsIdx);
		if ($dealingsResult === false) {
			throw new RollbackException('거래 테이블 조회 시 오류가 발생하였습니다.');
		}

		$rDealingsUserQ = 'SELECT `dealings_date`,
								  `dealings_writer_idx`,
								  `dealings_member_idx` 
							FROM `th_dealings_user` 
							WHERE `dealings_idx` = ?
							FOR UPDATE';

		// 거래 유저 테이블 조회
		$dealingsUserResult = $db->execute($rDealingsUserQ, $dealingsIdx);
		if ($dealingsUserResult === false) {
			throw new RollbackException('거래 유저 테이블 조회 시 오류가 발생했습니다.');
		}

		$dealingsMileage = $dealingsResult->fields['dealings_mileage']; // 거래금액
		$fixedDealingsMileage = $dealingsMileage;

		$commission = $dealingsResult->fields['commission']; // 수수료
		$itemNo = $dealingsResult->fields['item_no']; //상품권종류
		$dealingsWriterIdx = $dealingsUserResult->fields['dealings_writer_idx']; // 거래글 작성자
		$dealingsMemberIdx = $dealingsUserResult->fields['dealings_member_idx']; // 거래하는 사람
		
		$sellerMemberIdx = $buyerMemberIdx  = 0;

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
		
		// 구매자 쿠폰 사용내역
		$rUseageQ = 'SELECT `idx`,
							`coupon_member_idx`,
							`coupon_use_before_mileage`
					 FROM `th_coupon_useage`
					 WHERE `dealings_idx` = ?
					 AND `member_idx` = ?
					 AND `issue_type` = ?
					 AND `is_refund` = ?
					 FOR UPDATE';
		
		$rUseageResult = $db->execute($rUseageQ, $couponUseParam);
		if ($rUseageResult === false) {
			throw new RollbackException('구매자 쿠폰 사용내역을 조회 하면서 오류가 발생했습니다.');
		}

		$couponIdx = $rUseageResult->fields['idx'];
		$couponMemberIdx = $rUseageResult->fields['coupon_member_idx'];

		$commissionMemberIdx = 0;
		
		if (!empty($couponIdx)){
			$dealingsMileage = $rUseageResult->fields['coupon_use_before_mileage'];

			// 거래 완료시 쿠폰완료일자 표기 (구매자)
			$cpBuyerStParam = [
				'coupon_use_end_date'=> date('Y-m-d'),
				'is_refund'=> 'N',
				'idx'=> $couponIdx
			];

			$uBuyerCouponUseageQ = 'UPDATE `th_coupon_useage` SET 
										`coupon_use_end_date` = ?,
										`is_refund` = ?
										WHERE `idx` = ?';
				
			$uCouponUseageResult = $db->execute($uBuyerCouponUseageQ, $cpBuyerStParam);

			$uCpBuyerUseAffectRows = $db->affected_rows();
			if ($uCpBuyerUseAffectRows < 1) {
				throw new RollbackException('구매자 쿠폰 완료하는 과정에서 오류가 발생했습니다.');
			}
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

		// 판매자 쿠폰 사용내역 가져오기
		$rSellUseageQ = 'SELECT `idx`,
								`coupon_idx`,
								`coupon_use_mileage`
						 FROM `th_coupon_useage`
						 WHERE `dealings_idx` = ?
						 AND `member_idx` = ?
						 AND `issue_type` = ?
						 AND `is_refund` = ?
						 FOR UPDATE';
		
		$rSellUseageResult = $db->execute($rSellUseageQ, $commissionInfoParam);
		if ($rSellUseageResult === false) {
			throw new RollbackException('판매자 쿠폰 사용내역을 조회 하면서 오류가 발생했습니다.');
		}

		$sellCouponIdx = $rSellUseageResult->fields['coupon_idx'];

		// 쿠폰 정보 가져오기
		$rSellCouponQ = 'SELECT `discount_rate`,
								ROUND((`item_money` * `discount_rate`)/100) `discount_money`
						 FROM `th_coupon`
						 WHERE `idx` = ?';

		$rSellCouponResult = $db->execute($rSellCouponQ, $sellCouponIdx);
		if ($rSellCouponResult === false) {
			throw new RollbackException('쿠폰 정보를 조회 하면서 오류가 발생했습니다.');
		}

		$commisionCouponIdx = $rSellUseageResult->fields['idx'];
		$discountRate = $rSellCouponResult->fields['discount_rate'];

		if (!empty($commisionCouponIdx)) {
			// 할인쿠폰 사용
			$discountMoney = $rSellCouponResult->fields['discount_money'];
			$couponUseMileage = $rSellUseageResult->fields['coupon_use_mileage'];
			// 쿠폰 사용해서 100% 할인 받는 경우
			$commission -= $couponUseMileage;

			// 거래 완료시 쿠폰완료일자 표기 (판매자)
			$cpSellerStParam = [
				'coupon_use_end_date'=> $today,
				'is_refund'=> 'N',
				'idx'=>$commisionCouponIdx
			];

			$uCouponUseageQ = 'UPDATE `th_coupon_useage` SET 
								`coupon_use_end_date` = ?,
								`is_refund` = ?
								WHERE `idx` = ?';
				
			$uCouponUseageResult = $db->execute($uCouponUseageQ, $cpSellerStParam);
			$uCpUseAffectRows = $db->affected_rows();

			if ($uCpUseAffectRows < 1) {
				throw new RollbackException('판매자 쿠폰 완료하는 과정에서 오류가 발생했습니다.');
			}
		}

		$dealingsMileage -= $commission; // 최종금액
		$dealingsStatus = 4;

		if ($dealingsMileage > 0) {
			// 충전 파라미터
			$chargeParamGroup = [
				'charge_param' => [
					'member_idx'=> $sellerMemberIdx,
					'charge_infomation'=> '아이엠아이',
					'charge_account_no'=> setEncrypt($dealingsResult->fields['dealings_subject']),
					'charge_cost'=> $dealingsMileage,
					'spare_cost'=> $dealingsMileage,
					'charge_name'=> '관리자',
					'mileage_idx'=> $mileageType,
					'charge_date'=> $today,
					'charge_status'=> $chargeStatus
				],
				'dealings_idx'=> $dealingsIdx,
				'dealings_status'=> 4,
				'mileageType'=> $mileageType
			];

			// 충전하기
			$chargeResult = $mileageClass->chargeMileageProcess($chargeParamGroup);
			if ($chargeResult['result'] === false) {
				throw new RollbackException($chargeResult['resultMessage']);
			}

			// 거래 상태 파라미터
			$dealingsStPcParam = [
				'dealings_status'=> 4,
				'dealings_idx'=> $dealingsIdx
			];

			// 거래상태 관련
			$dealingsProcessResult = $dealingsClass->dealignsStatusProcess($dealingsStPcParam);
			if ($dealingsProcessResult['result'] === false) {
				throw new RollbackException($dealingsProcessResult['resultMessage']);
			}

			// 수수료 부과
			if ($commission > 0) {
				$rCommissionQ = 'SELECT `dealings_idx` 
								   FROM `th_dealings_commission`
								   WHERE `dealings_idx` = ?';

				$rCommissionResult = $db->execute($rCommissionQ, $dealingsIdx);
				if ($rCommissionResult === false) {
					throw new RollbackException('수수료 테이블을 조회 하는 중에 오류가 발생했습니다.');
				}

				$commissionDealingsIdx = $rCommissionResult->fields['dealings_idx'];
				if (!empty($commissionDealingsIdx)) {
					throw new RollbackException('이미 거래가 완료 되었습니다.');
				}

				$commissionParam = [
					'dealings_idx'=> $dealingsIdx,
					'commission'=> $commission,
					'sell_item_idx'=> $itemNo
				];

				$cCommissionQ = 'INSERT INTO `th_dealings_commission` SET
									`dealings_idx` = ?,
									`commission` = ?,
									`dealings_complete_date` = CURDATE(),
									`sell_item_idx` = ?';
			
				$cResult = $db->execute($cCommissionQ, $commissionParam);
				
				$inserId = $db->insert_id();
				if ($inserId < 1) {
					throw new RollbackException('수수료 등록하는데 오류가 발생했습니다.');
				}
			}
		}
		
		$db->completeTrans();
		
		// 페이백
		$db->startTrans();

		$purEventParam = [
			'member_idx'=> $buyerMemberIdx,
			'couponIdx'=> $couponIdx,
			'commisionCouponIdx'=> '',
			'CONF_EVENT_ARRAY'=> $CONFIG_EVENT_ARRAY,
			'itemNo'=> $itemNo
		];

		// 구매 이벤트에 참여 가능한지 조회
		$isPurEventResult = $eventClass->onProvideEvent($purEventParam);
		if ($isPurEventResult['result'] === false) {
			throw new RollbackException($isPurEventResult['resultMessage']);
		}
		
		// 페이백하기
		if ($isPurEventResult['isParticipatePurIngEvent'] === true) {
			// 이벤트 금액 계산
			$payback = $isPurEventResult['payback'];
			$paybackDealings = round($fixedDealingsMileage*$payback)/100;

			// 페이백 충전하기 
			$paybackTitle = setEncrypt($dealingsResult->fields['dealings_subject']." _페이백!!");

			$paybackGroup = $chargeParamGroup;

			$paybackGroup['charge_param']['charge_cost'] = $paybackDealings;
			$paybackGroup['charge_param']['spare_cost'] = $paybackDealings;
			$paybackGroup['charge_param']['mileage_idx'] = $isPurEventResult['paybackMileageType'];
			$paybackGroup['charge_param']['charge_account_no'] = $paybackTitle;
			$paybackGroup['charge_param']['member_idx'] = $buyerMemberIdx;

			$paybackGroup['mode'] = 'event';

			$payBackGpResult = $mileageClass->chargeMileageProcess($paybackGroup);
			if ($payBackGpResult['result'] === false) {
				throw new RollbackException($payBackGpResult['resultMessage']);
			}

			// 이벤트 히스토리 추가(구매)
			$historyParam = [
				'member_idx'=> $buyerMemberIdx,
				'event_type'=> '구매',
				'paybackDealings'=> $paybackDealings
			];

			$eventHistoryResult = $eventClass->addEventHistory($historyParam);
			if ($eventHistoryResult === false) {
				throw new RollbackException($eventHistoryResult['resultMessage']);
			}
		}

		$sellEventParam = [
			'member_idx'=> $sellerMemberIdx,
			'couponIdx'=> '',
			'commisionCouponIdx'=> $commisionCouponIdx,
			'CONF_EVENT_ARRAY'=> $CONFIG_EVENT_ARRAY,
			'itemNo'=> $itemNo
		];

		// 판매 이벤트에 참여 가능한지 조회
		$isSellEventResult = $eventClass->onProvideEvent($sellEventParam);
		if ($isSellEventResult['result'] === false) {
			throw new RollbackException($isSellEventResult['resultMessage']);
		}

		// 이벤트 히스토리 추가(판매)
		if ($isSellEventResult['isParticipateSellIngEvent'] === true) {
			$sellHistoryParam = [
				'member_idx'=> $sellerMemberIdx,
				'event_type'=> '판매',
				'paybackDealings'=> $commission
			];

			$eventSellHistoryResult = $eventClass->addEventHistory($sellHistoryParam);
			if ($eventSellHistoryResult === false) {
				throw new RollbackException($eventSellHistoryResult['resultMessage']);
			}
		}

		$returnUrl = SITE_DOMAIN . '/my_sell_list.php';
		$alertMessage = '정상적으로 거래가 완료되었습니다.';

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