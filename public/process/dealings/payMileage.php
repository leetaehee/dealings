<?php
	/**
	 * 상품권 거래 결제 (판매/구매)
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
		$couponClass = new CouponClass($db);

		$_POST['dealings_type'] = htmlspecialchars($_POST['dealings_type']);
		$_POST['dealings_mileage'] = htmlspecialchars($_POST['dealings_mileage']);
		if (isset($_POST['coupon_name'])) {
			$_POST['coupon_name'] = htmlspecialChars($_POST['coupon_name']);
		}

		$postData = $_POST;

		$memberIdx = $_SESSION['idx'];
		$dealingsIdx = $_SESSION['dealings_idx'];
		$dealingsStatus = $_SESSION['dealings_status']+1;

		$returnUrl = SITE_DOMAIN.'/mypage.php';

		if (!in_array($postData['dealings_type'], $CONFIG_COUPON_ISSUE_TYPE)) {
			throw new Exception('유효하지 않은 거래 타입입니다. 다시 시도하세요.');
		}

		$db->startTrans();
		
		// 쿠폰을 사용 한 경우 유효성 체크
		if (!empty($postData['coupon_name'])) {

			$couponMemberIdx = $postData['coupon_name'];

			// 해당 쿠폰이 실제 있는 쿠폰인지 체크
			$rCoponMbQ = 'SELECT `coupon_idx`,
								 `member_idx`,
								 `coupon_status`
						  FROM `th_coupon_member`
						  WHERE `idx` = ?
						  FOR UPDATE';
            
			$rCouponMbResult = $db->execute($rCoponMbQ, $couponMemberIdx);
			if ($rCouponMbResult === false) {
				throw new RollbackException('쿠폰의 고객 정보를 조회하면서 오류가 발생했습니다.');
			}

			$couponIdx = $rCouponMbResult->fields['coupon_idx'];
			$couponStatus = $rCouponMbResult->fields['coupon_status'];
			$couponMemberIdx = $rCouponMbResult->fields['member_idx'];

			if ($memberIdx != $couponMemberIdx) {
				throw new RollbackException('쿠폰 지급대상자가 아닙니다.');
			}

			if ($couponStatus == 2) {
				throw new RollbackException('선택하신 쿠폰은 이미 사용중입니다.');
			}

			$rCouponCheckP = [
				'idx'=> $couponIdx,
				'is_del'=> 'N'
			];
			
			$rCouponCheckQ = 'SELECT `idx`,
									 `discount_mileage`,
									 `discount_rate`,
									 `item_money`
							  FROM `th_coupon`
							  WHERE `idx` = ?
							  AND `is_del` = ?
							  FOR UPDATE';
			
			$rCouponCheckResult = $db->execute($rCouponCheckQ, $rCouponCheckP);
			if ($rCouponCheckResult === false) {
				throw new RollbackException('쿠폰의 키 값을 확인하는 중에 오류가 발생했습니다.');
			}

			$idx = $rCouponCheckResult->fields['idx'];
			if ($idx == null) {
				throw new RollbackException('유효하지 않은 쿠폰은 사용 할 수 없습니다.');
			}

			$discountRate = $rCouponCheckResult->fields['discount_rate'];
			$discountMileage = $rCouponCheckResult->fields['discount_mileage'];

			// 쿠폰 생성시 가격을 정해놓지 않은 경우 (모든가격)
			if ($discountMileage == 0) {
				$dealingsMileage = $postData['dealings_mileage'];
				$dealingsMileage -= round(($postData['dealings_mileage']*$discountRate)/100);
			}

			// 할인율이 100퍼센트 이하일때만 체크 (할인율 100%는 무료 구매)
			if ($discountRate == 100) {
				$dealingsMileage = 0;
			}
		} else {
			// 쿠폰을 사용하지 않은 경우
			$dealingsMileage = $postData['dealings_mileage']; 
		}

		// 거래금액 체크 
		$rDealingsMileageQ = 'SELECT `dealings_mileage` FROM `th_dealings` WHERE `idx` = ?';

		$rDealingsMileageResult = $db->execute($rDealingsMileageQ, $dealingsIdx);
		if ($rDealingsMileageResult === false) {
			throw new RollbackException('거래금액을 조회하면서 오류가 발생하였습니다.');
		}

		$checkDealingsMileage = $rDealingsMileageResult->fields['dealings_mileage'];
		if ($checkDealingsMileage != $postData['dealings_mileage']) {
			throw new RollbackException('거래 금액이 달라 결제를 진행 할 수 없습니다.');
		}

		// 회원 마일리지 조회 후 비교 
		$rMileageQ = 'SELECT `mileage` FROM `th_members` WHERE `idx` = ? FOR UPDATE';

		$rMileageResult = $db->execute($rMileageQ, $memberIdx);
		if ($rMileageResult === false) {
			throw new RollbackException('회원 마일리지를 조회 하면서 오류가 발생하였습니다.');
		}

		$totalMileage = $rMileageResult->fields['mileage'];
		if ($totalMileage > 0) {
			if (($dealingsMileage > $totalMileage) > 0) {
				throw new RollbackException('거래금액이 부족하여 결제를 진행 할 수 없습니다.');
			}
		}

		if ($totalMileage < 0) {
			throw new RollbackException('거래금액이 0보다 작을 수 없습니다.');
		}

		// 충전된 내역 중에서 우선순위가 가장 빠른 결제내역부터 정렬 
		$rChargeListP = [
			'member_idx'=> $memberIdx,
			'charge_status'=> 3
		];

		$rChargeListQ = 'SELECT `imc`.`idx`,
								`imc`.`charge_cost`,
								`imc`.`spare_cost`,
								`imc`.`mileage_idx`
						 FROM `th_mileage_charge` `imc`
							INNER JOIN `th_mileage` `im`
								ON `imc`.`mileage_idx` = `im`.`idx`
						 WHERE `imc`.`member_idx` = ?
						 AND `imc`.`charge_status` = ?
						 AND `imc`.`spare_cost` > 0
						 ORDER BY `im`.`priority` ,`imc`.`expiration_date` ASC, `imc`.`charge_date` ASC
						 FOR UPDATE';
		
		$rChargeListResult = $db->execute($rChargeListQ, $rChargeListP);
		if ($rChargeListResult === false) {
			throw new RollbackException('마일리지 충전 내역을 가져오는 중에 오류가 발생하였습니다.');
		}

		// 쿠폰을 써서 결제금액이 0원일 때는 차감하지 않는다.
		if ($dealingsMileage > 0 ) {
			$mileageP = [
				'mileageWithdrawalList'=> $rChargeListResult,
				'dealingsMileage'=> $dealingsMileage,
				'type'=> 'dealings',
				'member_idx'=> $_SESSION['idx'],
				'dealings_status'=> $dealingsStatus,
				'dealings_idx'=> $_SESSION['dealings_idx'],
				'dealings_writer_idx'=> $_SESSION['dealings_writer_idx'],
				'dealings_member_idx'=> $_SESSION['dealings_member_idx']
			];

			// 마일리지 결제 처리
			$payMileageResult = $mileageClass->withdrawalMileageProcess($mileageP);
			if ($payMileageResult['result'] === false) {
				throw new RollbackException($payMileageResult['resultMessage']);
			}
		}

		// 거래 상태 파라미터
		$dealingsStatusP = [
			'dealings_status'=> $dealingsStatus,
			'dealings_idx'=> $dealingsIdx
		];

		// 거래상태 관련
		$dealingsProcessResult = $dealingsClass->dealignsStatusProcess($dealingsStatusP);
		if ($dealingsProcessResult['result'] === false) {
			throw new RollbackException($dealingsProcessResult['resultMessage']);
		}

		// 쿠폰 사용내역에 추가 
		if (!empty($postData['coupon_name'])) {
			$useData = [
				'useageP' => [
					'type'=> '구매',
					'dealings_idx'=> $_SESSION['dealings_idx'],
					'coupon_idx'=> $couponIdx,
					'member_idx'=> $_SESSION['idx'],
					'coupon_use_before_mileage'=> $_POST['dealings_mileage'],
					'coupon_use_mileage'=> $dealingsMileage,
					'coupon_member_idx'=> $couponMemberIdx
				],
				'coupon_status_code'=> 2
			];

			// 쿠폰 상태 변경
			$couponStatusProcess = $couponClass->couponStatusProcess($useData);
			if ($couponStatusProcess['result'] === false) {
				throw new RollbackException($couponStatusProcess['resultMessage']);
			}
		}

		$_SESSION['dealings_writer_idx'] = '';
		$_SESSION['dealings_member_idx'] = '';
		$_SESSION['dealings_idx'] = '';
		$_SESSION['dealings_status'] = '';
		$_SESSION['purchaser_idx'] = '';
		$_SESSION['purchaser_mileage'] = '';
		
		$alertMessage = '결제가 완료되었습니다.';

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