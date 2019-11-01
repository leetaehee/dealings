<?php
	/**
	 * 상품권 거래 취소 (판매자시점)
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

		$commissionMemberIdx = 0;

		$couponStatusParam = [
			'dealings_idx'=> $dealingsIdx,
			'member_idx'=> $buyerMemberIdx,
			'issue_type'=> '구매',
			'coupon_use_end_date'=> $today,
			'is_refund'=> 'N'
		];

		// 구매자 쿠폰 환불처리
		$couponRefundResult = $couponClass->couponRefundProcess($couponStatusParam);
		if ($couponRefundResult['result'] === false) {
			throw new RollbackException($couponRefundResult['resultMessage']);
		}

		if (!empty($couponRefundResult['data']['couponIdx'])) {
			$couponUseMileage = $couponRefundResult['data']['couponUseMileage'];
			
			// 쿠폰 환불 처리(구매자) 
			if ($couponUseMileage > 0) {
				$dealingsMileage = $couponUseMileage;
			}

			if ($couponUseMileage == 0){
				$dealingsMileage = 0;
			}
		}

		$sellCouponStatusParam = [
			'dealings_idx'=> $dealingsIdx,
			'member_idx'=> $sellerMemberIdx,
			'issue_type'=> '판매',
			'coupon_use_end_date'=> date('Y-m-d'),
			'is_refund'=> 'N'
		];

		// 판매자 쿠폰 환불처리
		$couponSellRefundResult = $couponClass->couponRefundProcess($sellCouponStatusParam);
		if ($couponSellRefundResult['result'] === false) {
			throw new RollbackException($couponSellRefundResult['resultMessage']);
		}

		if ($dealingsMileage > 0) {
			// 충전 파라미터
			$chargeParamGroup = [
				'charge_param' => [
					'member_idx'=> $buyerMemberIdx,
					'charge_infomation'=> '아이엠아이',
					'charge_account_no'=> setEncrypt($dealingsResult->fields['dealings_subject']),
					'charge_cost'=> $dealingsMileage,
					'spare_cost'=> $dealingsMileage,
					'charge_name'=> '관리자',
					'mileage_idx'=> $mileageType,
					'charge_date'=> date('Y-m-d'),
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
				'dealings_status'=> 5,
				'dealings_idx'=> $dealingsIdx
			];

			// 거래상태 관련
			$dealingsProcessResult = $dealingsClass->dealignsStatusProcess($dealingsStPcParam);
			if ($dealingsProcessResult['result'] === false) {
				throw new RollbackException($dealingsProcessResult['resultMessage']);
			}
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