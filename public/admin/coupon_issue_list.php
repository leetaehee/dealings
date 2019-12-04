<?php
	/**
	 * 쿠폰 발행내역
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
		$title = TITLE_COUPON_ISSUE . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN . '/coupon.php';

		$alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// 쿠폰 발행내역 조회
        $rCouponQ = 'SELECT `subject`,
							`item_money`,
							`discount_rate`,
							`discount_mileage`,
							`issue_type`,
							`is_del`,
							`issue_date`,
							`start_date`,
							`expiration_date`,
							`sell_item_idx`
		             FROM `th_coupon`
		             ORDER BY `issue_date` DESC, `subject` ASC';

		$rCouponResult = $db->execute($rCouponQ);
		if ($rCouponResult === false) {
		    throw new Exception('쿠폰 발행내역을 조회하면서 오류가 발생했습니다.');
        }

		$couponData = [];

		foreach ($rCouponResult as $key => $value) {

		    $subject = $rCouponResult->fields['subject'];
		    $itemMoney = $rCouponResult->fields['item_money'];
		    $discountRate = $rCouponResult->fields['discount_rate'];
		    $discountMileage = $rCouponResult->fields['discount_mileage'];
		    $issueType = $rCouponResult->fields['issue_type'];
		    $isDel = $rCouponResult->fields['is_del'];
		    $issueDate = $rCouponResult->fields['issue_date'];
		    $startDate = $rCouponResult->fields['start_date'];
		    $expirationDate = $rCouponResult->fields['expiration_date'];
		    $sellItemIdx = $rCouponResult->fields['sell_item_idx'];

		    // 상품명 출력
            $rSellItemQ = 'SELECT `item_name`
                           FROM `th_sell_item`
                           WHERE `idx` = ?';

            $rSellItemResult = $db->execute($rSellItemQ, $sellItemIdx);
            if ($rSellItemResult === false) {
                throw new Exception('상품명을 조회하면서 오류가 발생했습니다.');
            }

            $itemName = $rSellItemResult->fields['item_name'];

            $couponData[] = [
                'seq'=> ($key+1),
                'subject'=> $subject,
                'item_money'=> $itemMoney,
                'discount_rate'=> $discountRate,
                'discount_mileage'=> $discountMileage,
                'issue_type'=> $issueType,
                'is_del'=> $isDel,
                'issue_date'=> $issueDate,
                'start_date'=> $startDate,
                'expiration_date'=> $expirationDate,
                'item_name'=> $itemName
            ];
        }

        $couponDataCount = count($couponData);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/coupon_issue_list.html.php';
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