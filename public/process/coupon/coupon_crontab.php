<?php
	/**
	 * 쿠폰 유효기간이 지날 경우 삭제
	 */

	$topDir = __DIR__.'/../../..';
	
	// 공통
	include_once $topDir . '/configs/config.php'; 
	include_once $topDir . '/messages/message.php'; 
	include_once $topDir . '/includes/function.php';

	// adodb
	include_once $topDir . '/adodb/adodb.inc.php';
	include_once $topDir . '/includes/adodbConnection.php';

	// Exception 파일
	include_once $topDir . '/Exception/RollbackException.php';

	try {
		$alertMessage = '';

		$db->startTrans();

		// 유효기간이 만료된 쿠폰이 존재하는지 확인
        $rCouponExpirationP = [
            'today'=> $today,
            'is_del'=> 'N'
        ];

        $rCouponExpirationCntQ = 'SELECT COUNT(`idx`) `cnt` 
                                  FROM `th_coupon` 
                                  WHERE `expiration_date` < ?  
                                  AND `is_del` = ?';

        $rCouponExpirationCntResult = $db->execute($rCouponExpirationCntQ, $rCouponExpirationP);
        if ($rCouponExpirationCntResult === false) {
            throw new RollbackException('유효기간 만료 쿠폰을 카운트하면서 오류가 발생했습니다.');
        }

        $couponExpirationCount = $rCouponExpirationCntResult->fields['cnt'];
        if ($couponExpirationCount == 0) {
            throw new RollbackException('유효기간이 만료된 쿠폰이 존재하지 않습니다.');
        }

        // 지급된 쿠폰 중에서 유효기간이 만료된 쿠폰을 추출한다.
        $rCouponExpirationQ = 'SELECT `idx`
                               FROM `th_coupon`
                               WHERE `expiration_date` < ?  
                               AND `is_del` = ?
                               FOR UPDATE';

        $couponExpirationResult = $db->execute($rCouponExpirationQ, $rCouponExpirationP);
        if ($couponExpirationResult === false) {
            throw new RollbackException('유효기간 만료 쿠폰을 조회하면서 오류가 발생했습니다.');
        }

        foreach($couponExpirationResult as $key => $value){
            $uCouponMbDeleteP = [
                'is_coupon_del'=> 'Y',
                'coupon_idx'=> $value['idx']
            ];

            $uCouponMbDeleteQ = 'UPDATE `th_coupon_member` SET 
                                   `is_coupon_del` = ?
                                 WHERE `coupon_idx` = ?';

            $uCouponMbDeleteResult = $db->execute($uCouponMbDeleteQ, $uCouponMbDeleteP);

            $couponMbAffectedRow = $db->affected_rows();
            if ($couponMbAffectedRow < 0) {
                throw new RollbackException('유효기간 만료 쿠폰을 삭제하면서 오류가 발생했습니다.');
            }
        }

        // 유효기간이 만료된 쿠폰 자동삭제
        $uCouponDeleteP = [
            'is_del'=> 'Y',
            'expiration_date'=> $today,
            'where_is_del'=> 'Y'
        ];

        $uCouponDeleteQ = 'UPDATE `th_coupon` SET 
						    `is_del` = ?
					       WHERE `expiration_date` < ?
					       AND `is_del` <> ?';

        $db->execute($uCouponDeleteQ, $uCouponDeleteP);

        $couponDeleteAffectedRow = $db->affected_rows();
        if ($couponDeleteAffectedRow < 0) {
            throw new RollbackException('유효기간이 만료된 쿠폰을 삭제하면서 오류가 발생했습니다.');
        }

		$alertMessage = '유효기간이 만료된 쿠폰 데이터를 삭제되었습니다.';

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
            echo $alertMessage;
		}
    }