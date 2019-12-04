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

	// Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/CouponClass.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_COUPON_PROVIDER_STATUS . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN.'/coupon.php'; // 리턴되는 화면 URL 초기화
		$btnName = '';

		$alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$getData = $_GET;

		$memberIdx = $getData['idx'];

        // 쿠폰 지급 된 내역을 조회
        $rCouponMbP = [
            'member_idx'=> $memberIdx,
            'is_del'=> 'N',
            'is_coupon_del'=> 'N'
        ];

        $rCouponMbQ = 'SELECT `issue_type`,
                              `idx` `coupon_mb_idx`,
                              `is_del`,
                              `coupon_idx`,
                              `sell_item_idx`
                       FROM `th_coupon_member`
                       WHERE `member_idx` = ?
                       AND `is_del` = ?
                       AND `is_coupon_del` = ?
                       ORDER BY `idx` DESC';

        $rCouponMbResult = $db->execute($rCouponMbQ, $rCouponMbP);
        if ($rCouponMbResult === false) {
            throw new Exception('쿠폰 지급 내역을 조회하면서 오류가 발생했습니다.');
        }

        // 쿠폰 지급 내역을 저장
        $couponMbData = [];

        foreach ($rCouponMbResult as $key => $value) {

            $issueType = $rCouponMbResult->fields['issue_type'];
            $couponMbIdx = $rCouponMbResult->fields['coupon_mb_idx'];
            $isDel = $rCouponMbResult->fields['is_del'];
            $sellItemIdx = $rCouponMbResult->fields['sell_item_idx'];
            $couponIdx = $rCouponMbResult->fields['coupon_idx'];

            // 상품명 조희
            $rSellItemQ = 'SELECT `item_name`
                           FROM `th_sell_item`
                           WHERE `idx` = ?';

            $rSellItemResult = $db->execute($rSellItemQ, $sellItemIdx);
            if ($rSellItemResult === false) {
                throw new Exception('상품명을 조회하면서 오류가 발생했습니다.');
            }

            $itemName = $rSellItemResult->fields['item_name'];

            // 쿠폰정보 조회
            $rCouponQ = 'SELECT `subject`,
                                `discount_rate`,
                                `discount_mileage`,
                                `item_money`
                         FROM `th_coupon`
                         WHERE `idx` = ?';

            $rCouponResult = $db->execute($rCouponQ, $couponIdx);
            if ($rCouponResult === false) {
                throw new Exception('쿠폰 정보를 조회하면서 오류가 발생했습니다.');
            }

            $subject = $rCouponResult->fields['subject'];
            $discountRate = $rCouponResult->fields['discount_rate'];
            $discountMileage = $rCouponResult->fields['discount_rate'];
            $itemMoney = $rCouponResult->fields['item_money'];

            // 쿠폰 사용정보 조회
            $rCouponUseP = [
                'coupon_member_idx'=> $couponMbIdx,
                'is_refund'=> 'N'
            ];

            $rCouponUseQ = 'SELECT `idx` `use_idx`,
                                   `coupon_use_mileage`
                            FROM `th_coupon_useage`
                            WHERE `coupon_member_idx` = ?
                            AND `is_refund` = ?';

            $rCouponUseResult = $db->execute($rCouponUseQ, $rCouponUseP);
            if ($rCouponUseResult === false) {
                throw new Exception('쿠폰 사용 정보를 조회하면서 오류가 발생했습니다.');
            }

            $useIdx = $rCouponUseResult->fields['use_idx'];
            $couponUseMileage = $rCouponUseResult->fields['coupon_use_mileage'];

            // 쿠폰 수정&삭제 시 필요한 파라미터
            $couponUrlParam = 'idx=' .$couponMbIdx. '&coupon_idx=' .$couponIdx. '&member_idx=' .$memberIdx;

            // 발급한 쿠폰삭제 URL
            $couponDeleteUrl = COUPON_PROCEE_ACTION . '/delete_coupon.php?' . $couponUrlParam;
            // 발급한 쿠폰수정 URL
            $couponUpdateUrl =  SITE_ADMIN_DOMAIN . '/member_coupon_update.php?' . $couponUrlParam;

            $couponMbData[] = [
                'seq'=> ($key+1),
                'issue_type'=> $issueType,
                'coupon_member_idx'=> $couponMbIdx,
                'coupon_idx'=> $couponIdx,
                'item_name'=> $itemName,
                'subject'=> $subject,
                'discount_rate'=> $discountRate,
                'discount_mileage'=> $discountMileage,
                'item_money'=> $itemMoney,
                'is_del'=> $isDel,
                'use_idx'=> $useIdx,
                'coupon_use_mileage'=> $couponUseMileage,
                'coupon_delete_url'=> $couponDeleteUrl,
                'coupon_update_url'=> $couponUpdateUrl
            ];
        }

        $couponMbDataCount = count($couponMbData);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/coupon_provider_status.html.php';
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