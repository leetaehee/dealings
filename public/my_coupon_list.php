<?php
	/**
	 * 회원이 사용 가능한 쿠폰 조회 기능
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
		$title = TITLE_MY_COUPON_LIST . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN . '/voucher_dealings.php';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$memberIdx = $_SESSION['idx'];

		// 마이페이지에서 확인하는 나의 쿠폰 내역 조회
        $rMyCouponP = [
            'is_coupon_del'=> 'N',
            'is_del'=> 'N',
            'member_idx'=> $memberIdx
        ];

        $rMyCouponQ = 'SELECT `issue_type`,
                              `idx`,
                              `coupon_status`,
                              `sell_item_idx`,
                              `coupon_idx`
                       FROM `th_coupon_member`
                       WHERE `is_coupon_del` = ?
                       AND `is_del` = ?
                       AND `member_idx` = ?';

        $rMyCouponResult = $db->execute($rMyCouponQ, $rMyCouponP);
        if ($rMyCouponResult === false) {
            throw new Exception('지급된 쿠폰내역을 조회하면서 오류가 발생했습니다.');
        }

        foreach ($rMyCouponResult as $key => $value) {
            // 판매물품 고유정보
            $sellItemIdx = $value['sell_item_idx'];
            // 쿠폰 고유정보
            $couponIdx = $value['coupon_idx'];
            // 쿠폰 상태
            $couponStatus = $value['coupon_status'];
            // 쿠폰 타입
            $issueType = $value['issue_type'];

            // 판매물품명 조회
            $rSellItemQ = 'SELECT `item_name`
                           FROM `th_sell_item`
                           WHERE `idx` = ?';

            $rSellItemResult = $db->execute($rSellItemQ, $sellItemIdx);
            if ($rSellItemResult === false) {
                throw new Exception('판매물품명을 조회하면서 오류가 발생했습니다.');
            }

            $itemName = $rSellItemResult->fields['item_name'];

            // 쿠폰정보 출력
            $rCouponQ = 'SELECT `subject`,
                                `discount_rate`,
                                `discount_mileage`,
                                `item_money`,
                                `start_date`,
                                `expiration_date`
                         FROM `th_coupon`
                         WHERE `idx` = ?';

            $rCouponResult = $db->execute($rCouponQ, $couponIdx);
            if ($rCouponResult === false) {
                throw new Exception('쿠폰 정보를 조회하면서 오류가 발생했습니다.');
            }

            $subject = $rCouponResult->fields['subject'];
            $discountRate = $rCouponResult->fields['discount_rate'];
            $discountMileage = $rCouponResult->fields['discount_mileage'];
            $itemMoney = $rCouponResult->fields['item_money'];
            $startDate = $rCouponResult->fields['start_date'];
            $expirationDate = $rCouponResult->fields['expiration_date'];
            
            // 화면에 보여주어야 할 데이터 배열에 저장.
            $myCouponData[] = [
                'seq'=> $key+1,
                'issue_type'=> $issueType,
                'subject'=> $subject,
                'discount_rate'=> $discountRate,
                'discount_mileage'=> $discountMileage,
                'item_money'=> $itemMoney,
                'start_date'=> $startDate,
                'expiration_date'=> $expirationDate,
                'item_name'=> $itemName,
                'coupon_status'=> $couponStatus,
            ];
        }

        $myCouponCount = count($myCouponData);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/my_coupon_list.html.php';
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