<?php
	/**
	 * 지급 된 쿠폰을 수정(다른쿠폰으로 교체)
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	// Exception 파일 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$returnUrl = SITE_ADMIN_DOMAIN; // 리턴되는 화면 URL 초기화.
        $alertMessage = '';

		if ($connection === false) {
           throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// injection, xss 방지코드
		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$_GET['coupon_idx'] = htmlspecialchars($_GET['coupon_idx']);
		$getData = $_GET;

		$idx = $getData['idx'];
		$couponIdx = $getData['coupon_idx'];

		// returnURL
		$returnUrl = SITE_ADMIN_DOMAIN . '/coupon.php';

		if (empty($idx) && empty($couponIdx)) {
			throw new Exception('비정상적인 접근입니다.'); 
		}
		
		$db->startTrans();

		// 쿠폰을 수정하기 위해 사용자가 선택한 쿠폰 체크 (삭제가 되었는지 사용중인지 체크)
        $rCouponMbQ = 'SELECT `idx`,
                              `is_del`,
                              `is_coupon_del`,
                              `coupon_status`
					   FROM `th_coupon_member` 
					   WHERE `idx` = ?
					   FOR UPDATE';

        $rCouponMbResult = $db->execute($rCouponMbQ, $idx);
        if ($rCouponMbResult === false) {
            throw new RollbackException('지급된 쿠폰 상태를 조회하면서 오류가 발생했습니다.');
        }

        // 지급된 쿠폰의 유효성 체크
        $couponMbIdx = $rCouponMbResult->fields['idx'];
        if (empty($couponMbIdx)) {
            throw new RollbackException('지급된 쿠폰의 정보가 올바르지 않습니다.');
        }

        // 지급된 쿠폰 삭제 유무
        $isDel = $rCouponMbResult->fields['is_del'];
        if ($isDel == 'Y') {
            throw new RollbackException('선택하신 쿠폰은 삭제가 되었습니다.');
        }

        // 쿠폰 삭제 유무
        $isCouponDel = $rCouponMbResult->fields['is_coupon_del'];
        if ($isCouponDel == 'Y') {
            throw new RollbackException('발행 된 쿠폰이 삭제가 되었습니다.');
        }

        // 쿠폰의 상태
        $couponStatus = $rCouponMbResult->fields['coupon_status'];
        if ($couponStatus != 1) {
            throw new RollbackException('쿠폰은 이미 사용 중입니다.');
        }

        // 쿠폰 정보 조회
        $rCouponQ = 'SELECT `idx`,
							`issue_type`,
							`subject`,
							`item_money`,
							`discount_rate`,
							`discount_mileage`,
							`sell_item_idx`,
							`is_del`
					  FROM `th_coupon`
				      WHERE `idx`= ?
				      FOR UPDATE';

        $rCouponResult = $db->execute($rCouponQ, $couponIdx);
        if ($rCouponResult === false) {
            throw new RollbackException('쿠폰 정보를 조회하면서 오류가 발생하였습니다.');
        }

        // 지급된 쿠폰을 다른 쿠폰으로 수정.
        $uCouponMbP = [
            'issue_type'=> $rCouponResult->fields['issue_type'],
            'coupon_idx'=> $rCouponResult->fields['idx'],
            'sell_item_idx'=> $rCouponResult->fields['sell_item_idx'],
            'subject'=> $rCouponResult->fields['subject'],
            'discount_rate'=> $rCouponResult->fields['discount_rate'],
            'item_money'=> $rCouponResult->fields['item_money'],
            'is_del'=> $rCouponResult->fields['is_del'],
            'coupon_member_idx'=> $idx
        ];

        $uCouponMbQ = 'UPDATE `th_coupon_member` SET 
                          `issue_type` = ?,
                          `coupon_idx` = ?,
                          `sell_item_idx` = ?,
                          `subject` = ?,
                          `discount_rate` = ?,
                          `item_money` = ?,
                          `is_del` = ?
                          WHERE `idx` = ?';

        $uCouponMbResult = $db->execute($uCouponMbQ, $uCouponMbP);

        $couponMbAffectedRow = $db->affected_rows();
        if ($couponMbAffectedRow < 1) {
            throw new RollbackException('지급된 쿠폰을 수정하면서 오류가 발생했습니다.');
        }

		$returnUrl = SITE_ADMIN_DOMAIN . '/courpon_provider_status.php?idx=' . $getData['idx'];
		
		$alertMessage = '지급 된 쿠폰정보가 수정되었습니다.';

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
            alertMsg(SITE_ADMIN_DOMAIN, 0);
        }
    }