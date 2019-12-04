<?php
	/**
	 * 지급된 쿠폰을 수정할 수 있는 화면.
	 */

	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	try {
		$title = TITLE_COUPON_MODIFY . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN . '/coupon.php';

        $alertMessage = '';

		if ($connection === false) {
           throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// 쿠폰 수정 화면에서 오류 발생 시 처리.
        $returnUrl = SITE_ADMIN_DOMAIN . '/coupon.php';

		// injection, xss 방지코드
		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$_GET['coupon_idx'] = htmlspecialchars($_GET['coupon_idx']);
		$_GET['member_idx'] = htmlspecialchars($_GET['member_idx']);
		$getData = $_GET;

		$idx = $getData['idx'];
		$memberIdx = $getData['member_idx'];
		$couponIdx = $getData['coupon_idx'];

		if (empty($idx)) {
			throw new Exception('비정상적인 접근입니다.'); 
		}

		// 사용자에게 지급된 쿠폰 정보 조회
        $rCouponDelQ = 'SELECT `is_del`,
                               `idx`
                        FROM `th_coupon_member` 
                        WHERE `idx` = ?';

		$rCouponMbResult = $db->execute($rCouponDelQ, $idx);
		if ($rCouponMbResult === false) {
		    throw Exception('지급된 쿠폰 삭제 여부를 조회하면서 오류가 발생했습니다.');
        }

		$isDel = $rCouponMbResult->fields['is_del'];
		$idx = $rCouponMbResult->fields['idx'];

		// 쿠폰이 이미 삭제 되었는지 체크
		if ($isDel == 'Y') {
		    throw new Exception('해당 쿠폰은 관리자에 의해 삭제가 되었습니다.');
        }

		// 쿠폰정보가 유효한지 체크
        if (empty($idx)) {
            throw new Exception('유효하지 않은 쿠폰 정보입니다.');
        }

        // 쿠폰 사용내역 조회
        $rCouponUseP = [
            'coupon_member_idx'=> $idx,
            'coupon_idx'=> $couponIdx,
            'is_refund'=> 'N'
        ];

        $rCouponUseQ = 'SELECT `idx` 
                        FROM `th_coupon_useage`
                        WHERE `coupon_member_idx` = ?
                        AND `coupon_idx` = ?
                        AND `is_refund` = ?';

        $rCouponUseResult = $db->execute($rCouponUseQ, $rCouponUseP);
        if ($rCouponUseResult === false) {
            throw new Exception('쿠폰 사용내역을 조회하면서 오류가 발생했습니다.');
        }

        $couponUseIdx = $rCouponUseResult->fields['idx'];
        if (!empty($couponUseIdx)) {
            throw new Exception('쿠폰 사용내역이 존재하여 삭제 할 수 없습니다.');
        }

        $rCouponP = [
            'is_del'=> 'N'
        ];

        // 쿠폰정보 조회
        $rCouponQ = 'SELECT `idx`,
                            `issue_type`,
                            `subject`,
                            `item_money`,
                            `discount_rate`,
                            `discount_mileage`,
                            `sell_item_idx`
                     FROM `th_coupon`
                     WHERE `is_del` = ?
                     ORDER BY `issue_date` DESC';

        $rCouponResult = $db->execute($rCouponQ, $rCouponP);
        if ($rCouponResult === false) {
            throw new Exception('쿠폰 정보를 조회하면서 오류가 발생했습니다.');
        }

        // 수정 가능한 쿠폰 내역 추가
        $couponModifyData = [];

        foreach ($rCouponResult as $key => $value) {

            $issueType = $value['issue_type'];
            $subject = $value['subject'];
            $itemMoney = $value['item_money'];
            $discountRate = $value['discount_rate'];
            $discountMileage = $value['discount_mileage'];
            $sellItemIdx = $value['sell_item_idx'];
            $couponNewIdx = $value['idx'];

            // 지급된 쿠폰 내역 조회
            $rCouponMbP = [
                'coupon_idx'=> $couponNewIdx,
                'member_idx'=> $memberIdx,
                'is_del'=> 'N'
            ];

            $rCouponMbQ = 'SELECT `idx`,
                                  `coupon_idx` 
                           FROM `th_coupon_member`
                           WHERE `coupon_idx` = ?
                           AND `member_idx` = ?
                           AND `is_del` = ?';

            $rCouponMbResult = $db->execute($rCouponMbQ, $rCouponMbP);
            if ($rCouponMbResult === false) {
                throw new Exception('지급된 쿠폰 내역을 조회하면서 오류가 발생했습니다.');
            }

            // 이미 지급된 쿠폰은 노출 되지 않도록 함
            $couponMbIdx = $rCouponMbResult->fields['idx'];
            if (!empty($couponMbIdx)) {
                continue;
            }

            // 쿠폰에 연결된 상품명 조회
            $rSellItemQ = 'SELECT `item_name`
                           FROM `th_sell_item`
                           WHERE `idx` = ?';

            $rSellItemResult = $db->execute($rSellItemQ, $sellItemIdx);
            if ($rSellItemResult === false) {
                throw new Exception('쿠폰에 연결된 상품명을 조회하면서 오류가 발생했습니다.');
            }

            $itemName = $rSellItemResult->fields['item_name'];

            // 쿠폰 변경을 처리하는 PROCESS URL
            $couponUpdateURL = COUPON_PROCEE_ACTION;
            $couponUpdateURL .= '/update_coupon.php?idx=' . $idx . '&coupon_idx=' . $couponNewIdx;

            $couponModifyData[] = [
                'seq'=> ($key+1),
                'coupon_member_idx'=> $couponMbIdx,
                'issue_type'=> $issueType,
                'subject'=> $subject,
                'item_money'=> $itemMoney,
                'discount_rate'=> $discountRate,
                'discount_mileage'=> $discountMileage,
                'item_name'=> $itemName,
                'coupon_update_url'=> $couponUpdateURL
            ];
        }

        $couponModifyDataCount = count($couponModifyData);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/member_coupon_update.html.php';
	} catch (Exception $e) {
		// 트랜잭션을 사용하지 않을 때
		$alertMessage = $e->getMessage();
    } finally {
        if  ($connection === true) {
            $db->close();
        }
        if (!empty($alertMessage)) {
            alertMsg($returnUrl, 1, $alertMessage);
        }
    }
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_coupon.html.php';// 전체 레이아웃