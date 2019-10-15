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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/CouponClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/SellItemClass.php';

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
		$sellItemClass = new SellItemClass($db);
		$couponClass = new CouponClass($db);

		// injection, xss 방지코드
		$_POST['dealings_state'] = htmlspecialchars($_POST['dealings_state']);
		$_POST['dealings_type'] = htmlspecialchars($_POST['dealings_type']);
		$_POST['dealings_subject'] = htmlspecialchars($_POST['dealings_subject']);
		$_POST['dealings_content'] = htmlspecialchars($_POST['dealings_content']);
		$_POST['item_no'] = htmlspecialchars($_POST['item_no']);
		$_POST['item_money'] = htmlspecialchars($_POST['item_money']);
		$_POST['dealings_mileage'] = htmlspecialchars($_POST['dealings_mileage']);
		$_POST['memo'] = htmlspecialchars($_POST['memo']);
		
		if (isset($_POST['coupon_name'])){
			$_POST['coupon_name'] = htmlspecialchars($_POST['coupon_name']);
		}

		$itemObjectNo = null;
		if (isset($_POST['item_object_no'])) {
			$_POST['item_object_no'] = htmlspecialchars($_POST['item_object_no']);
			if (isset($_POST['item_object_no'])) {
				$itemObjectNo = setEncrypt($_POST['item_object_no']);
			}
		}

		$postData = $_POST;
		$returnUrl = SITE_DOMAIN . '/voucher_dealings.php';

		// 폼 데이터 받아서 유효성 검증 실패시 리다이렉트 경로
		if ($postData['dealings_state'] !== '거래대기') {
			throw new Exception('유효하지 않은 거래 상태입니다. 다시 시도하세요.');
		}

		$dealingsTypeArray = ['구매', '판매'];
		if (!in_array($postData['dealings_type'], $dealingsTypeArray)) {
			throw new Exception('유효하지 않은 거래 타입입니다. 다시 시도하세요.');
		}

		$resultDealingsValidCheck = $dealingsClass->checkDealingFormValidate($postData);
		if ($resultDealingsValidCheck['isValid'] == false) {
			// 유효성 오류
			throw new Exception($resultDealingsValidCheck['errorMessage']);
		}

		$db->startTrans();

		if (!empty($postData['coupon_name'])) {

			// 해당 쿠폰이 실제 있는 쿠폰인지 체크
			$couponMemberIdx =  $postData['coupon_name'];

			$memberCouponData = [
				'coupon_member_idx'=> $couponMemberIdx,
				'is_del'=> 'N'
			];

			$couponIdx = $couponClass->getCheckCouponMemberIdx($memberCouponData, $isUseForUpdate);
			if ($couponIdx === false) {
				throw new RollbackException('쿠폰의 고객 정보를 가져오면서 오류가 발생했습니다.');
			}

			$validParam = [
				'issue_type'=> '판매',
				'idx'=> $couponIdx,
				'is_del'=> 'N'
			];
			
			$isValidCoupon = $couponClass->getCheckValidCoupon($validParam, $isUseForUpdate);
			if ($isValidCoupon === false) {
				throw new RollbackException('쿠폰의 키 값을 검사하는 중에 오류가 발생했습니다.');
			}

			if ($isValidCoupon == null) {
				throw new RollbackException('유효하지 않은 쿠폰은 사용 할 수 없습니다.');
			}

			if ($isValidCoupon === false) {
				throw new RollbackException('쿠폰에 적용된 가격을 가져오면서 오류가 발생했습니다.');
			}

			// 쿠폰을 사용했는지 체크 
			$availableCouponParam = [
				'idx'=> $couponIdx,
				'is_del'=> 'N',
				'member_idx'=> $_SESSION['idx'],
				'is_refund'=> 'N'
			];

			$isAvailableCoupon = $couponClass->getCheckAvailableCoupon($availableCouponParam, $isUseForUpdate);
			if ($isAvailableCoupon === false) {
				throw new RollbackException('쿠폰 사용 내역을 조회하는 중에 오류가 발생했습니다.');
			}

			if ($isAvailableCoupon != null) {
				throw new RollbackException('해당 쿠폰은 이미 사용했습니다.');
			}

			$couponDiscountData = $couponClass->getCouponDiscountData($couponIdx, $isUseForUpdate);
			if ($couponDiscountData === false) {
				throw rollbackException('쿠폰 할인 정보 가져오면서 오류가 발생했습니다.');
			}

			$discountRate = $couponDiscountData->fields['discount_rate'];
		}

		$today = date('Y-m-d');
		$expiration_date = date('Y-m-d',strtotime('+5 day',strtotime($today))); // 만료일

		// 수수료 가져오기 
		$itemIdx = $postData['item_no'];

		$commission = $sellItemClass->getCheckSellItemValue($itemIdx, $isUseForUpdate);
		if ($commission === false) {
			throw new RollbackException('수수료 데이터를 가져 오지 못했습니다.');
		} 
		
		if ($commission == '') {
			throw new RollbackException('수수료 데이터를 조회 할 수 없습니다.');
		}

		// 거래상태 가져오기
		$dealingsStatus = $dealingsClass->getDealingsStatus($postData['dealings_state']);
		if ($dealingsStatus === false) {
			throw new RollbackException('거래 테이블 상태를 가져올 수 없습니다.');
		}

		$insertData = [
			'expiration_date'=> $expiration_date,
			'register_date'=> $today,
			'dealings_type'=> $postData['dealings_type'],
			'dealings_subject'=> $postData['dealings_subject'],
			'dealings_content'=> $postData['dealings_content'],
			'item_no'=> $postData['item_no'],
			'item_money'=> $postData['item_money'],
			'item_object_no'=> $itemObjectNo,
			'dealings_mileage'=> $postData['dealings_mileage'],
			'dealings_commission'=> $commission,
			'dealings_status'=> $dealingsStatus,
			'memo'=> $postData['memo'],
			'idx'=> $_SESSION['idx']
		];

		$insertResult = $dealingsClass->insertDealings($insertData);
		if ($insertResult < 1) {
			throw new RollbackException('거래데이터 생성 실패하였습니다.');
		}

		$processData = [
			'dealings_idx'=> $insertResult,
			'dealings_status_idx'=> $dealingsStatus
		];

		$insertProcessResult = $dealingsClass->insertDealingsProcess($processData);
		if ($insertProcessResult < 1) {
			throw new RollbackException('거래 처리과정 생성 실패하였습니다.');
		}

		// 구매 시 쿠폰 적용했다면 사용내역에 데이터 생성.
		if (!empty($postData['coupon_name'])) {

			// 원래 내야 하는 수수료
			$couponUseBeforeMileage = round(($postData['dealings_mileage']*$commission)/100);
			// 쿠폰을 적용 받아서 내야 하는 수수료 
			$couponUseMileage = round(($couponUseBeforeMileage*$discountRate)/100);

			$useageData = [
				'type'=> '판매',
				'dealings_idx'=> $insertResult,
				'coupon_idx'=> $couponIdx,
				'member_idx'=> $_SESSION['idx'],
				'coupon_use_before_mileage'=> $couponUseBeforeMileage,
				'coupon_use_mileage'=> $couponUseMileage,
				'coupon_member_idx'=> $couponMemberIdx
			];

			$insertUseageDataResult = $couponClass->insertCouponUseage($useageData);
			if ($insertUseageDataResult < 1) {
				throw new RollbackException('쿠폰 사용 내역을 입력하는 중에 오류가 발생했습니다.');
			}

			$couponStatusName = '사용완료';

			$couponStatusCode = $couponClass->getCouponStatusCode($couponStatusName, $isUseForUpdate);
			if ($couponStatusCode === false) {
				throw new RollbackException('쿠폰 상태 코드를 가져오면서 오류가 발생했습니다.');
			}

			if (empty($couponStatusCode)) {
				throw new RollbackException('쿠폰 상태 코드를 찾을 수 없습니다.');
			}

			$couponMbStParam = [
				'coupon_status'=> $couponStatusCode,
				'idx'=> $couponMemberIdx
			];

			$updateCouponMbStatusResult = $couponClass->updateCouponMemberStatus($couponMbStParam);
			if ($updateCouponMbStatusResult < 1) {
				throw new RollbackException('쿠폰 상태 코드를 변경하면서 오류가 발생했습니다.');
			}
		}

		$returnUrl = SITE_DOMAIN.'/voucher_dealings.php';
		$alertMessage = '정상적으로 거래글이 등록되었습니다.';

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
	