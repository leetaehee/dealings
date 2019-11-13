<?php
	/**
	 * 상품권 거래 > 상품권 거래하기 > 상품권 구매목록에서 판매하기 클릭했을 때 처리.
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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/CouponClass.php';

	// Exception 파일 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화.
        $alertMessage = '';
		$isUseForUpdate = true;

		if ($connection === false) {
           throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$dealingsClass = new DealingsClass($db);
		$couponClass = new CouponClass($db);

		$_POST['dealings_type'] = htmlspecialchars($_POST['dealings_type']);

		if(isset($_POST['coupon_name'])){
			$_POST['coupon_name'] = htmlspecialChars($_POST['coupon_name']);
		}
		$postData = $_POST;

		$memberIdx = $_SESSION['idx'];
		$dealingsIdx = $_SESSION['dealings_idx'];

		// returnURL
		$returnUrl = SITE_DOMAIN . '/voucher_dealings.php';

		$dealingsTypeArray = ['구매', '판매'];
		if (!in_array($postData['dealings_type'], $dealingsTypeArray)) {
			throw new Exception('유효하지 않은 거래 타입입니다. 다시 시도하세요.');
		}

		$db->startTrans();

		// 사용자가 쿠폰을 선택한 경우 검증하기
		if (!empty($postData['coupon_name'])) {

			// 쿠폰 지급 고유키
			$couponMemberIdx =  $postData['coupon_name'];

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

			if ($memberIdx != $_SESSION['idx']) {
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

			$rCommissionQ = 'SELECT `dealings_commission` 
							 FROM `th_dealings` 
							 WHERE `idx` = ?
							 FOR UPDATE';

			$rCommissionResult = $db->execute($rCommissionQ, $dealingsIdx);
			if ($rCommissionResult === false) {
				throw new RollbackException('수수료를 조회하면서 오류가 발생했습니다.');
			}

			$dealingsCommission = $rCommissionResult->fields['dealings_commission'];

			// 원래 내야 하는 수수료
			$couponUseBeforeMileage = ($_SESSION['dealings_mileage']*$dealingsCommission)/100;
			// 쿠폰을 적용 받아서 내야 하는 수수료 
			$couponUseMileage = ($couponUseBeforeMileage*$discountRate)/100;
		}

		// 거래 데이터 중복 체크 
		$rDealingsExistP = [
			'idx'=>$_SESSION['dealings_idx'],
			'dealinges_status'=>$_SESSION['dealings_status']
		];

		$rDealingsExistQ = 'SELECT count(`idx`) cnt 
							FROM `th_dealings`
							WHERE `idx` = ?
							AND `dealings_status` = ?';
			
		$rDealingsExistResult = $db->execute($rDealingsExistQ, $rDealingsExistP);
		if ($rDealingsExistResult === false) {
			throw new RollbackException('거래 이중 등록 체크 하는 중에 오류가 발생했습니다.');
		}

		$existCount = $rDealingsExistResult->fields['cnt'];
		if ($existCount == 0) {
			throw new RollbackException('이미 거래가 완료 되었습니다.');
		}

		// 거래 상태 파라미터
		$dealingsStPcParam = [
			'dealings_status'=> $_SESSION['dealings_status'],
			'dealings_idx'=> $dealingsIdx,
            'member_idx'=> $memberIdx
		];

		// 거래상태 관련
		$dealingsProcessResult = $dealingsClass->dealignsStatusProcess($dealingsStPcParam);
		if ($dealingsProcessResult['result'] === false) {
			throw new RollbackException($dealingsProcessResult['resultMessage']);
		}

		// 쿠폰 상태 변경
		if (!empty($postData['coupon_name'])) {
			$useData = [
				'useageP' => [
					'type'=> '판매',
					'dealings_idx'=> $_SESSION['dealings_idx'],
					'coupon_idx'=> $couponIdx,
					'member_idx'=> $_SESSION['idx'],
					'coupon_use_before_mileage'=> $couponUseBeforeMileage,
					'coupon_use_mileage'=> $couponUseMileage,
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
		$_SESSION['dealings_idx'] = '';
		$_SESSION['dealigng_status'] = '';
		$_SESSION['dealingsMileage'] = '';

		$returnUrl = SITE_DOMAIN.'/voucher_dealings.php';
		$alertMessage = '정상적으로 거래 상태가 변경되었습니다.';

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