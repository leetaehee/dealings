<?php
	/**
	 * 회원에게 쿠폰 지급 
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

    // Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/CouponClass.php';

	// Exception 파일 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$returnUrl = SITE_ADMIN_DOMAIN; // 리턴되는 화면 URL 초기화.
        $alertMessage = '';
		$isUseForUpdate = true; 

		if ($connection === false) {
           throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$couponClass = new CouponClass($db);

		// injection, xss 방지코드
		$_GET['member_idx'] = htmlspecialchars($_GET['member_idx']);
		$_GET['coupon_idx'] = htmlspecialchars($_GET['coupon_idx']);
		$getData = $_GET;

		// returnURL
		$returnUrl = SITE_ADMIN_DOMAIN . '/coupon.php';

		if (empty($getData['member_idx'])) {
			throw new Exception('비정상적인 접근입니다.'); 
		}

		$db->startTrans();

        $couponIdx = $getData['coupon_idx'];

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
                      WHERE `idx` = ?
                      FOR UPDATE';

        $rCouponResult = $db->execute($rCouponQ, $couponIdx);
        if ($rCouponResult === false) {
            throw new RollbackException('쿠폰 정보를 조회하면서 오류가 발생하였습니다.');
        }

        // 쿠폰이 이미 지급이 되었는지 체크
        $rCouponOverlapP = [
            'coupon_idx'=> $couponIdx,
            'member_idx'=> $getData['member_idx'],
            'issue_type'=> $rCouponResult->fields['issue_type'],
            'is_coupon_del'=> 'N',
            'is_del'=> 'N'
        ];

        $rCouponOverlapQ = 'SELECT COUNT(`idx`) `cnt`
					        FROM `th_coupon_member`
					        WHERE `coupon_idx` = ?
					        AND `member_idx` = ?
					        AND `issue_type` = ?
					        AND `is_coupon_del` = ?
					        AND `is_del` = ?';

        $rCouponOverlapResult = $db->execute($rCouponOverlapQ, $rCouponOverlapP);
        if ($rCouponOverlapResult === false) {
            throw new RollbackException('쿠폰 중복 지급을 체크하면서 오류가 발생했습니다.');
        }

        $couponOverlapCnt = $rCouponOverlapResult->fields['cnt'];
        if ($couponOverlapCnt > 0) {
            throw new RollbackException('이미 쿠폰이 지급되었습니다.');
        }

        // 쿠폰을 지급
        $cCouponMemberP = [
            'issue_type'=> $rCouponResult->fields['issue_type'],
            'coupon_idx'=> $rCouponResult->fields['idx'],
            'sell_item_idx'=> $rCouponResult->fields['sell_item_idx'],
            'member_idx'=> $getData['member_idx'],
            'subject'=> $rCouponResult->fields['subject'],
            'discount_rate'=> $rCouponResult->fields['discount_rate'],
            'item_money'=> $rCouponResult->fields['item_money'],
            'coupon_status'=> 1
        ];

        $cCouponMemberQ = 'INSERT INTO `th_coupon_member` SET 
                            `issue_type` = ?,
                            `coupon_idx` = ?,
                            `sell_item_idx` = ?,
                            `member_idx` = ?,
                            `subject` = ?,
                            `discount_rate` = ?,
                            `item_money` = ?,
                            `coupon_status` = ?';

        $cCouponMemberResult = $db->execute($cCouponMemberQ, $cCouponMemberP);

        $cCouponMemberInsertId = $db->insert_id();
        if ($cCouponMemberInsertId < 1) {
           throw new RollbackException('쿠폰을 지급하는 중에 오류가 발생했습니다.');
        }

        $returnUrl = SITE_ADMIN_DOMAIN . '/coupon_member_status.php';

		$alertMessage = '정상적으로 쿠폰이 지급이 되었습니다.';

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