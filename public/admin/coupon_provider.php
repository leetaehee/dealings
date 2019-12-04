<?php
	/**
	 * 쿠폰 발행하기
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
		$title = TITLE_COUPON_PROVIDER . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN . '/coupon.php';
		$JsTemplateUrl = JS_ADMIN_URL . '/.js';
		$btnName = '';

		$alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$getData = $_GET;

		$memberIdx = $getData['idx'];

		// 발행 가능한 쿠폰 조회
        $rCouponMbP = [
            'member_idx'=> $memberIdx,
            'is_del'=> 'N',
            'is_coupon_del'=> 'N'
        ];

        $rCouponMbQ = 'SELECT `tc`.`idx`,
							  `tc`.`issue_type`,
							  `tc`.`subject`,
							  `tc`.`item_money`,
							  `tc`.`discount_rate`,
							  `tc`.`discount_mileage`,
							  `tc`.`sell_item_idx`
                       FROM `th_coupon` `tc`
                          LEFT JOIN `th_coupon_member` `tcm`
                            ON `tc`.`idx` = `tcm`.`coupon_idx`
                            AND `tcm`.`member_idx` = ?
							AND `tcm`.`is_del` = ?
                       WHERE `tc`.`is_del` = ?
                       AND `tcm`.`idx` IS NULL
                       ORDER BY `tc`.`issue_date` ASC';

        $rCouponMbResult = $db->execute($rCouponMbQ, $rCouponMbP);
        if ($rCouponMbResult === false) {
            throw new Exception('밣행 가능한 쿠폰을 조회하면서 오류가 발생했습니다.');
        }

        // 발행가능한 쿠폰 추가
        $issueCouponData = [];

        foreach ($rCouponMbResult as $key => $value) {
            $couponIdx = $rCouponMbResult->fields['idx'];
            $issueType = $rCouponMbResult->fields['issue_type'];
            $subject = $rCouponMbResult->fields['subject'];
            $itemMoney = $rCouponMbResult->fields['item_money'];
            $discountRate = $rCouponMbResult->fields['discount_rate'];
            $discountMileage = $rCouponMbResult->fields['discount_mileage'];
            $sellItemIdx = $rCouponMbResult->fields['sell_item_idx'];

            // 쿠폰이 사용가능한 상품명 조회
            $rSellItemQ = 'SELECT `item_name`
                           FROM `th_sell_item`
                           WHERE `idx`= ?';

            $rSellItemResult = $db->execute($rSellItemQ, $sellItemIdx);
            if ($rSellItemResult === false) {
                throw new Exception('상품명을 조회하면서 오류가 발생했습니다.');
            }

            $itemName = $rSellItemResult->fields['item_name'];

            // 쿠폰 발급해주는 URL
            $couponAddUrl = COUPON_PROCEE_ACTION . '/add_member_coupon.php';
            $couponAddUrl .= '?member_idx=' . $memberIdx . '&coupon_idx=' . $couponIdx;

            $issueCouponData[] = [
                'seq'=> ($key+1),
                'couponIdx'=> $couponIdx,
                'issue_type'=> $issueType,
                'subject'=> $subject,
                'item_money'=> $itemMoney,
                'discount_rate'=> $discountRate,
                'discount_mileage'=> $discountMileage,
                'item_name'=> $itemName,
                'coupon_add_url'=> $couponAddUrl
            ];
        }

        $issueCouponDataCount = count($issueCouponData);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/coupon_provider.html.php';
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