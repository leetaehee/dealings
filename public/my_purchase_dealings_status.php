<?php
	/**
	 * 마이룸 > 구매 > 구매등록물품
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
		$title = TITLE_VOUCHER_PURCHASE_ENROLL_STATUS . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN . '/mypage.php';
		$alertMessage = '';

		$actionUrl = DEALINGS_PROCESS_ACCTION . '/payMileage.php';
		$JsTemplateUrl = JS_URL . '/my_purchase_dealings_status.js';
		$dealingsType = '판매';
		$btnName = '결제하기';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// xss, injection 방지
		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$_GET['type'] = htmlspecialchars($_GET['type']);
		$getData = $_GET;

		$dealingsIdx = $getData['idx'];
		$memberIdx = $_SESSION['idx'];

        $rDealingsQ = 'SELECT `register_date`,
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
            throw new Exception('거래 정보를 조회하면서 오류가 발생했습니다.');
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
        $dealingsMemo = $rDealingsResult->fields['memo'];

        // 구매자 조회
        $rPurchaserQ = 'SELECT `id`,
                               `name`
                        FROM `th_members`
                        WHERE `idx` = ?';

        $rPurchaserResult = $db->execute($rPurchaserQ, $writerIdx);
        if ($rPurchaserResult === false) {
            throw new Exception('구매자를 조회하면서 오류가 발생했습니다.');
        }

        $purchaserId = $rPurchaserResult->fields['id'];
        $purchaserName = $rPurchaserResult->fields['name'];

        // 구매 물품명 조회
        $rSellItemQ = 'SELECT `item_name`
                       FROM `th_sell_item`
                       WHERE `idx` = ?';

        $rSellItemResult = $db->execute($rSellItemQ, $itemNo);
        if ($rSellItemResult === false) {
            throw Exception('구매 물품 이름을 조회하면서 오류가 발생했습니다.');
        }

        $itemName = $rSellItemResult->fields['item_name'];

        // 거래 상태 조회
        $rDealingsStatusQ = 'SELECT `dealings_status_name`
                             FROM `th_dealings_status_code`
                             WHERE `idx` = ?';

        $rDealingsStatusResult = $db->execute($rDealingsStatusQ, $dealingsStatus);
        if ($rDealingsStatusResult === false) {
            throw new Exception('거래 상태를 조회하면서 오류가 발생했습ㄴ디.');
        }

        $dealingsStatusName = $rDealingsStatusResult->fields['dealings_status_name'];

        // 거래 유저 조회 (판매자)
        $rDealingsUserQ = 'SELECT `dealings_member_idx`
                           FROM `th_dealings_user`
                           WHERE `dealings_idx` = ?';

        $rDealingsUserResult = $db->execute($rDealingsUserQ, $dealingsIdx);
        if ($rDealingsUserResult === false) {
            throw new Exception('판매자 키를 조회하면서 오류가 발생했습니다.');
        }

        $sellerIdx = $rDealingsUserResult->fields['dealings_member_idx'];

        // 판매자 정보 조회
        $rSellerQ = 'SELECT `idx`,
                            `id`,
                            `name`,
                            `email`,
                            `phone`
                     FROM `th_members`
                     WHERE `idx` = ?';

        $rSellerResult = $db->execute($rSellerQ, $sellerIdx);
        if ($rSellerResult === false) {
            throw new Exception('판매자 정보를 조회하면서 오류가 발생했습니다.');
        }

        $sellerIdx = $rSellerResult->fields['idx'];
        $sellerId = $rSellerResult->fields['id'];
        $sellerName = $rSellerResult->fields['name'];
        $sellerEmail = $rSellerResult->fields['email'];
        $sellerPhone = $rSellerResult->fields['phone'];

        // 구매자가 이용 가능한 마일리지 확인
        $rMileageQ = 'SELECT `mileage` 
                      FROM `th_members` 
                      WHERE `idx` = ?';

        $rMileageResult = $db->execute($rMileageQ, $memberIdx);
        if ($rMileageResult === false) {
            throw new Exception('구매자 마일리지를 조회하면서 오류가 발생했습니다.');
        }

        $totalMileage = $rMileageResult->fields['mileage'];

        // 이용가능한 쿠폰 가져오기
        $rAvailableCouponP = [
            'sell_item_idx'=> $itemNo,
            'issue_type'=> '구매',
            'item_money'=> $itemMoney,
            'is_coupon_del'=> 'N',
            'is_del'=> 'N',
            'member_idx'=> $memberIdx,
            'coupon_status'=> 1
        ];

        $rAvailableCouponQ = 'SELECT `idx`,
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

        $rAvailableCouponResult = $db->execute($rAvailableCouponQ, $rAvailableCouponP);
        if ($rAvailableCouponResult === false) {
            throw new Exception('사용 가능한 쿠폰을 조회하면서 오류가 발생했습니다.');
        }

        $couponData = $rAvailableCouponResult;
        $couponDataCount = $couponData->recordCount();

        // 사용한 쿠폰정보 가져오기
        $rUseCouponP = [
            'dealings_idx'=> $dealingsIdx,
            'member_idx'=> $memberIdx,
            'issue_type'=> '구매',
            'is_refund'=> 'N'
        ];

        $rUseCouponQ = 'SELECT `coupon_idx`,
                               `coupon_use_before_mileage`,
							   `coupon_use_mileage`
					    FROM `th_coupon_useage`
					    WHERE `dealings_idx` = ?
					    AND `member_idx` = ?
					    AND `issue_type` = ?
					    AND `is_refund` = ?';

        $rUseCouponResult = $db->execute($rUseCouponQ, $rUseCouponP);
        if ($rUseCouponResult === false) {
            throw new Exception('사용한 쿠폰내역을 조회하면서 오류가 발생했습니다.');
        }

        $useCouponIdx = $rUseCouponResult->fields['coupon_idx'];
        $useCpBeforeMileage = $rUseCouponResult->fields['coupon_use_before_mileage'];
        $useCpUsedMileage = $rUseCouponResult->fields['coupon_use_mileage'];

        // 사용된 쿠폰 정보를 조회
        $rUseCouponInfoQ = 'SELECT `subject`,
                                   `item_money`,
                                   `discount_rate`
                            FROM `th_coupon`
                            WHERE `idx` = ?';

        $rUseCouponInfoResult = $db->execute($rUseCouponInfoQ, $useCouponIdx);
        if ($rUseCouponInfoResult == false) {
            throw new Exception('사용된 쿠폰의 정보를 조회하면서 오류가 발생했습니다.');
        }

        $useCpSubject = $rUseCouponInfoResult->fields['subject'];
        $useCpItemMoney = $rUseCouponInfoResult->fields['item_money'];
        $useCpDiscountRate = $rUseCouponInfoResult->fields['discount_rate'];

        if ($useCpItemMoney == 0) {
            // 100% 할인 받았을 때
            $useCpDiscountMoney = ($dealingsMileage * $useCpDiscountRate)/100;
        } else {
            $useCpDiscountMoney = ($useCpItemMoney * $useCpDiscountRate)/100;
        }

        // 세션 등록
        $_SESSION['purchaser_idx'] = $writerIdx;
        $_SESSION['purchaser_mileage'] = $dealingsMileage;
        $_SESSION['dealings_writer_idx'] = $writerIdx;
        $_SESSION['dealings_member_idx'] = $sellerIdx;
        $_SESSION['dealings_idx'] = $dealingsIdx;
        $_SESSION['dealings_status'] = $dealingsStatus;

        // 구매거래수정
        $dealingsModifyUrl = SITE_DOMAIN . '/purchase_dealings_modify.php';
        $dealingsModifyUrl .= '?idx=' . $getData['idx'];

        // 거래삭제
        $dealingsDeleteUrl = DEALINGS_PROCESS_ACCTION . '/dealings_delete.php';
        $dealingsDeleteUrl .= '?idx=' . $getData['idx'];

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/my_purchase_dealings_status.html.php';
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