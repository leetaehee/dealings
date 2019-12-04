<?php
	/*
	 * 판매 결제 상세 화면 
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
		$title = TITLE_VOUCHER_SELL_STATUS . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN . '/mypage.php';
		$alertMessage = '';

		$actionUrl = DEALINGS_PROCESS_ACCTION . '/payMileage.php';
		$JsTemplateUrl = JS_URL . '/dealings_sell_status_view.js';
		$dealingsType = '판매';
		$btnName = '결제하기';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// xss, injection 방지
		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$_GET['type'] = htmlspecialchars($_GET['type']);
		$getData = $_GET;

        // 거래테이블 고유키
        $dealingsIdx = $getData['idx'];

        // 거래정보 가져오기
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
            throw new Exception('거래 데이터를 조회하면서 오류가 발생했습니다.');
        }

        $writerIdx = $rDealingsResult->fields['writer_idx'];
        $itemNo = $rDealingsResult->fields['item_no'];
        $dealingsStatus = $rDealingsResult->fields['dealings_status'];
        $registerDate = $rDealingsResult->fields['register_date'];
        $expirationDate = $rDealingsResult->fields['expiration_date'];
        $dealingsSubject = $rDealingsResult->fields['dealings_subject'];
        $dealingsContent = $rDealingsResult->fields['dealings_content'];
        $itemMoney = $rDealingsResult->fields['item_money'];
        $itemObjectNo = $rDealingsResult->fields['item_object_no'];
        $dealingsMileage = $rDealingsResult->fields['dealings_mileage'];
        $dealingsCommission = $rDealingsResult->fields['dealings_commission'];
        $memo = $rDealingsResult->fields['memo'];

        // 판매자 정보 가져오기
        $rSellerQ = 'SELECT `name`,
                           `id`
                    FROM `th_members`
                    WHERE `idx` = ?';

        $rSellerResult = $db->execute($rSellerQ, $writerIdx);
        if ($rSellerResult === false) {
            throw new Exception('판매자 정보를 조회하면서 오류가 발생했습니다.');
        }

        $name = $rSellerResult->fields['name'];
        $id = $rSellerResult->fields['id'];

        // 상품권명 가져오기
        $rSellItemQ = 'SELECT `item_name`
                       FROM `th_sell_item`
                       WHERE `idx` = ?';

        $rSellItemResult = $db->execute($rSellItemQ, $itemNo);
        if ($rSellItemResult === false) {
            throw new Exception('상품권명을 조회하면서 오류가 발생했습니다.');
        }

        $itemName = $rSellItemResult->fields['item_name'];

        // 거래유저정보 조회
        $rDealingsUserQ = 'SELECT `dealings_member_idx`
                           FROM `th_dealings_user`
                           WHERE `dealings_idx` = ?';

        $rDealingsUserResult = $db->execute($rDealingsUserQ, $dealingsIdx);
        if ($rDealingsUserResult === false) {
            throw new Exception('거래 유저를 조회하면서 오류가 발생햇습니다.');
        }

        $dealingsMemberIdx = $rDealingsUserResult->fields['dealings_member_idx'];

        // 거래 상태명 가져오기
        $rDealingsStatusQ = 'SELECT `dealings_status_name`
                             FROM `th_dealings_status_code`
                             WHERE `idx` = ?';

        $rDealingsStatusResult = $db->execute($rDealingsStatusQ, $dealingsStatus);
        if ($rDealingsStatusResult === false) {
            throw new Exception('거래 상태이름을 조회하면서 오류가 발생했습니다.');
        }

        $dealingsStatusName = $rDealingsStatusResult->fields['dealings_status_name'];

        // 구매자 정보 가져오기
        $rPurchaserQ = 'SELECT `idx`,
							   `mileage`,
							   `name`,
							   `phone`,
							   `email`,
							   `id`
                        FROM `th_members` `imi`
						WHERE `idx` = ?';

        $rPurchaserResult = $db->execute($rPurchaserQ, $dealingsMemberIdx);
        if ($rPurchaserResult == false) {
           throw new Exception('구매자 정보를 조회하면서 오류가 발생했습니다.');
        }

        $purchaserIdx = $rPurchaserResult->fields['idx'];
        $purchaserMileage = $rPurchaserResult->fields['mileage'];
        $purchaserName = $rPurchaserResult->fields['name'];
        $purchaserPhone = $rPurchaserResult->fields['phone'];
        $purchaserEmail = $rPurchaserResult->fields['email'];
        $purchaserId = $rPurchaserResult->fields['id'];

        // 사용 가능한 쿠폰 조회
        $rCouponMbP = [
            'sell_item_idx'=> $itemNo,
            'issue_type'=> '구매',
            'item_money'=> $itemMoney,
            'is_coupon_del'=> 'N',
            'is_del'=> 'N',
            'member_idx'=> $_SESSION['idx'],
            'coupon_status'=> 1
        ];

        $rCouponMbQ = 'SELECT `idx`,
							   `subject`,
							   `item_money`,
							   `discount_rate`
					   FROM `th_coupon_member`
					   WHERE `sell_item_idx` IN (?,5)  
					   AND `issue_type` = ?
					   AND `item_money` IN (?,0)
                       AND `is_coupon_del` = ?
					   AND `is_del` = ?
                       AND `member_idx` = ?
					   AND `coupon_status` = ?';

        $rCouponMbResult = $db->execute($rCouponMbQ, $rCouponMbP);
        if ($rCouponMbResult === false) {
            throw new Exception('지급 된 쿠폰을 조회하면서 오류가 발생했습니다.');
        }

        $couponMbResultCount = $rCouponMbResult->recordCount();

        // 쿠폰사용내역 조회
        $rCouponUseP = [
            'dealings_idx'=> $getData['idx'],
            'member_idx'=> $_SESSION['idx'],
            'issue_type'=> '구매',
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
            $couponDiscountRate = $rCouponResult->fields['discount_rate'];
            $couponItemMoney = $rCouponResult->fields['item_money'];
            $couponDiscountMoney = $rCouponResult->fields['discount_money'];
        }

        // 세션등록
        $_SESSION['dealings_writer_idx'] = $writerIdx;
        $_SESSION['dealings_member_idx'] = $dealingsMemberIdx;
        $_SESSION['dealings_idx'] = $dealingsIdx;
        $_SESSION['dealings_status'] = $getData['type'];
        $_SESSION['purchaser_idx'] = $purchaserIdx;
        $_SESSION['purchaser_mileage'] = $purchaserMileage;

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/dealings_sell_status_view.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if ($connection === true) {
			$db->close();
		}

		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃