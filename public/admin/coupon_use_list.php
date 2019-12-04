<?php
	/**
	 * 쿠폰 사용내역 
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php';
	
	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	try {
		$title = TITLE_COUPON_USEAGE . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN . '/coupon.php';

		$alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// 쿠폰 사용내역 조회
        $rCouponUseQ = 'SELECT `coupon_use_mileage`,
							   `coupon_use_before_mileage`,
							   `issue_type`,
							   `member_idx`,
							   `coupon_idx`,
							   `is_refund`,
							   `coupon_use_end_date`
                        FROM `th_coupon_useage`
                        ORDER BY `coupon_use_end_date` DESC, `coupon_member_idx` DESC';

        $rCouponUseResult = $db->execute($rCouponUseQ);
        if ($rCouponUseResult === false) {
            throw new Exception('쿠폰 사용내역을 조회하면서 오류가 발생했습니다.');
        }

        // 쿠폰 사용내역 추가
        $couponUseData = [];

        foreach ($rCouponUseResult as $key => $value) {

            $couponUseMileage = $value['coupon_use_mileage'];
            $couponUseBeforeMileage = $value['coupon_use_before_mileage'];
            $issueType = $value['issue_type'];
            $couponIdx = $value['coupon_idx'];
            $memberIdx = $value['member_idx'];
            $isRefund = $value['is_refund'];
            $couponUseEndDate = $value['coupon_use_end_date'];

            // 쿠폰 정보 조회
            $rCouponQ = 'SELECT `subject`,
                                `item_money`,
                                `discount_rate`,
                                `sell_item_idx`,
                                `discount_mileage`
                         FROM `th_coupon`
                         WHERE `idx` = ?';

            $rCouponResult = $db->execute($rCouponQ, $couponIdx);
            if ($rCouponResult === false) {
                throw new Exception('쿠폰 정보를 조회하면서 오류가 발생했습니다.');
            }

            $sellItemIdx = $rCouponResult->fields['sell_item_idx'];
            $subject = $rCouponResult->fields['subject'];
            $itemMoney = $rCouponResult->fields['item_money'];
            $discountRate = $rCouponResult->fields['discount_rate'];
            $discountMileage = $rCouponResult->fields['discount_mileage'];

            // 쿠폰에 적용된 상품 조회
            $rSellItemQ = 'SELECT `item_name`
                           FROM `th_sell_item`
                           WHERE `idx` = ?';

            $rSellItemResult = $db->execute($rSellItemQ, $sellItemIdx);
            if ($rSellItemResult === false) {
                throw new Eception('쿠폰에 적용된 상품명을 조회하면서 오류가 발생했습니다.');
            }

            $itemName = $rSellItemResult->fields['item_name'];

            // 쿠폰 사용한 사람의 이름을 조회
            $rMemberQ = 'SELECT `name`
                         FROM `th_members`
                         WHERE `idx` = ?';

            $rMemberResult = $db->execute($rMemberQ, $memberIdx);
            if ($rMemberResult === false) {
                throw new Exception('쿠폰을 사용자명을 조회하면서 오류가 발생했습니다.');
            }

            $name = setDecrypt($rMemberResult->fields['name']);

            $couponUseData[] = [
                'seq'=> ($key+1),
                'coupon_use_mileage'=> number_format($couponUseMileage),
                'coupon_use_before_mileage'=> number_format($couponUseBeforeMileage),
                'issue_type'=> $issueType,
                'coupon_idx'=> $couponIdx,
                'name'=> $name,
                'subject'=> $subject,
                'item_money'=> $itemMoney,
                'discount_rate'=> number_format($discountRate),
                'discount_mileage'=> $discountMileage,
                'item_name'=> $itemName,
                'is_refund'=> $isRefund,
                'coupon_use_end_date'=> $couponUseEndDate
            ];
        }

        $couponUseDataCount = count($couponUseData);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/coupon_use_list.html.php';
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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_coupon.html.php';// 전체 레이아웃