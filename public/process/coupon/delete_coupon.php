<?php
	/**
	 * 지급된 쿠폰을 삭제
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
		$returnUrl = SITE_ADMIN_DOMAIN;
        $alertMessage = '';

		if ($connection === false) {
           throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// injection, xss 방지코드
		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$_GET['coupon_idx'] = htmlspecialchars($_GET['coupon_idx']);
		$_GET['member_idx'] = htmlspecialchars($_GET['member_idx']);
		$getData = $_GET;

		// returnURL
		$returnUrl = SITE_ADMIN_DOMAIN . '/coupon.php';

		if (empty($getData['idx'])) {
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

        $rCouponMbResult = $db->execute($rCouponMbQ, $getData['idx']);
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

        // 지급된 쿠폰을 삭제
        $uCouponDeleteP = [
            'is_del'=> 'Y',
            'idx'=> $getData['idx']
        ];

        $uCouponDeleteQ = 'UPDATE `th_coupon_member` SET 
						      `is_del` = ?
					        WHERE `idx` = ?';

        $uCouponDeleteResult = $db->execute($uCouponDeleteQ, $uCouponDeleteP);

        $couponDeleteAffectedRow = $db->affected_rows();
        if ($couponDeleteAffectedRow < 0) {
            throw new RollbackException('쿠폰을 삭제하면서 오류가 발생하였습니다.');
        }

		$returnUrl = SITE_ADMIN_DOMAIN . '/coupon_member_status.php';

		$alertMessage = '지급된 쿠폰이 정상적으로 삭제되었습니다.';

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