<?php
	/**
	 * 판매중인물품 
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php';
	
	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	try {
		$title = TITLE_VOUCHER_PURCHASE_STATUS . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN.'/mypage.php'; // 리턴되는 화면 URL 초기화
		$alertMessage = '';

		$actionUrl = DEALINGS_PROCESS_ACCTION . '/changeCancelStatus.php';
		$JsTemplateUrl = JS_URL . '/dealings_purchase_status_view.js';
		$dealingsType = '판매';
		$btnName = '판매취소';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// xss, injection 방지
		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$_GET['type'] = htmlspecialchars($_GET['type']);
		$getData = $_GET;

		$dealingsIdx = $getData['idx'];

		// 거래정보 조회
        $rDealingsQ = 'SELECT `idx`,
							  `dealings_type`,
							  `register_date`,
							  `expiration_date`,
							  `dealings_subject`,
							  `dealings_content`,
							  `item_money`,
							  `item_no`,
							  `item_object_no`,
							  `dealings_mileage`,
							  `dealings_commission`,
							  `dealings_status`,
							  `writer_idx`,
							  `memo`
					   FROM `th_dealings`
					   WHERE `idx` = ?';

        $rDealingsResult = $db->execute($rDealingsQ, $dealingsIdx);
        if ($rDealingsResult === false) {
            throw new Exception('거래정보를 조회하면서 오류가 발생했습니다.');
        }

        $registerDate = $rDealingsResult->fields['register_date'];
        $expirationDate = $rDealingsResult->fields['expiration_date'];
        $dealingsSubject = $rDealingsResult->fields['dealings_subject'];
        $dealingsContent = $rDealingsResult->fields['dealings_content'];
        $itemMoney = $rDealingsResult->fields['item_money'];
        $itemNo = $rDealingsResult->fields['item_no'];
        $itemObjectNo = $rDealingsResult->fields['item_object_no'];
        $dealingsMileage = $rDealingsResult->fields['dealings_mileage'];
        $dealingsCommission = $rDealingsResult->fields['dealings_commission'];
        $dealingsStatus = $rDealingsResult->fields['dealings_status'];
        $writerIdx = $rDealingsResult->fields['writer_idx'];

        // 구매자 정보 출력
        $rPurchaserQ = 'SELECT `name`,
							   `id`
					    FROM `th_members`
					    WHERE `idx` = ?';

        $rPurchaserResult = $db->execute($rPurchaserQ, $writerIdx);
        if ($rDealingsResult === false) {
            throw new Exception('구매자를 조회하면서 오류가 발생했습니다.');
        }

        $name = $rPurchaserResult->fields['name'];
        $id = $rPurchaserResult->fields['id'];

        // 판매되는 물품명 출력
        $rSellItemQ = 'SELECT `item_name`
                       FROM `th_sell_item`
                       WHERE `idx` = ?';

        $rSellItemResult = $db->execute($rSellItemQ, $itemNo);
        if ($rSellItemResult === false) {
            throw RollbackException('판매 물품을 조회하면서 오류가 발생했습니다.');
        }

        $itemName = $rSellItemResult->fields['item_name'];

        // 거래상태 이름 출력
        $rDealingsStatusQ = 'SELECT `dealings_status_name`
                             FROM `th_dealings_status_code`
                             WHERE `idx` = ?';

        $rDealingsStatusResult = $db->execute($rDealingsStatusQ, $dealingsStatus);
        if ($rDealingsStatusResult === false) {
            throw new Exception('거래상태명을 조회하면서 오류가 발생했습니다.');
        }

        $dealingsStatusName = $rDealingsStatusResult->fields['dealings_status_name'];

        // 쿠폰 사용내역 조회
        $rCouponUseP = [
            'dealings_idx'=> $dealingsIdx,
            'member_idx'=> $_SESSION['idx'],
            'issue_type'=> '판매',
            'is_refund'=> 'N'
        ];

        $rCouponUseQ = 'SELECT `idx`,
                               `coupon_idx`,
							   `coupon_member_idx`,
							   `coupon_use_before_mileage`,
							   `coupon_use_mileage`
                        FROM `th_coupon_useage`
                        WHERE `dealings_idx` = ?
                        AND `member_idx` = ?
                        AND `issue_type` = ?
                        AND `is_refund` = ?';

        $rCouponUseResult = $db->execute($rCouponUseQ, $rCouponUseP);
        if ($rCouponUseResult === false) {
            throw new Exception('쿠폰 사용내역을 조회하면서 오류가 발생했습니다.');
        }

        $couponIdx = $rCouponUseResult->fields['coupon_idx'];
        $discountRate = $couponUseMileage = 0;

        // 적용된 쿠폰이 있을 경우에만
        if (!empty($couponIdx)) {
            $couponUseIdx = $rCouponUseResult->fields['idx'];
            $couponMbIdx = $rCouponUseResult->fields['coupon_member_idx'];
            $couponBeforeMileage = $rCouponUseResult->fields['coupon_use_before_mileage'];
            $couponUseMileage = $rCouponUseResult->fields['coupon_use_mileage'];

            // 쿠폰정보 조희
            $rCouponQ = 'SELECT `subject`,
                                `discount_rate`,
                                `item_money`,
                                ROUND((`item_money` * `discount_rate`)/100) `discount_money`
                         FROM `th_coupon`
                         WHERE `idx` = ?';
                $rCouponResult = $db->execute($rCouponQ, $couponIdx);
            if ($rCouponResult === false) {
                throw new Exception('쿠폰을 조회하면서 오류가 발생했습니다.');
            }

            $couponSubject = $rCouponResult->fields['subject'];
            $discountRate = $rCouponResult->fields['discount_rate'];
            $couponMoney = $rCouponResult->fields['item_money'];
            $discountMoney = $rCouponResult->fields['discount_money'];
        }

        // 거래잔액
        $finalPaymentSum = $finalRealPaymentSum = $dealingsMileage;

        // 수수료 입력 되지 않았을 때 처리
        if ($dealingsCommission < 1) {
            $dealingsCommission = 0;
        }

        // 거래금액에서 수수료를 차감
        if ($dealingsCommission > 0) {
            // 판매금액에서 내야 할 수수료 계산
            $commission = ceil(($finalPaymentSum*$dealingsCommission)/100);
            $finalPaymentSum -= $commission;
        }

        // 적용된 쿠폰이 있는 경우 거래금액에서 쿠폰할인을 한다. 100% 할인은 제외.
        if (!empty($couponIdx) && $discountRate < 100) {
            $finalRealPaymentSum -= $couponUseMileage;
            $discountMoney =  $useCouponData->fields['coupon_use_mileage'];
        }

        // 세션 등록
        $_SESSION['dealings_writer_idx'] = $writerIdx;
        $_SESSION['dealings_idx'] = $dealingsIdx;
        $_SESSION['dealings_status'] = $getData['type'];

        $dealingsCompleteParam = '?idx=' . $getData['idx'] . '&target=member_idx'; // 파라미터
        $dealingsApproval = DEALINGS_PROCESS_ACCTION . '/seller_dealings_approval.php' . $dealingsCompleteParam; // 거래승인
        $dealingsCancel = DEALINGS_PROCESS_ACCTION . '/seller_dealings_cancel.php' . $dealingsCompleteParam; // 거래취소

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/dealings_purchase_status_view.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃