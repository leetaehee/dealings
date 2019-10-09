<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 
	 *	1. 쿠폰 유효기간이 지날 경우 삭제
	 *	2. `imi_coupon`에 expiration_date가 현재 날짜보다 작으면 `is_del` 을 'Y'(삭제)변경한다.
	 */

	$topDir = __DIR__.'/../../..';

	include_once $topDir . '/configs/config.php'; // 환경설정
	include_once $topDir . '/messages/message.php'; // 메세지
	include_once $topDir . '/includes/function.php'; // 공통함수

	// adodb
	include_once $topDir . '/adodb/adodb.inc.php';
	include_once $topDir . '/includes/adodbConnection.php';
	
	// Class 파일
	include_once $topDir . '/class/CouponClass.php'; 

	// Exception 파일
	include_once $topDir . '/Exception/RollbackException.php';

	try {
		$today = date('Y-m-d');
		$alertMessage = '';

		$db->startTrans();

        $couponClass = new CouponClass($db);

		$param = [
			'today'=> $today,
			'is_del'=>'Y'
		];

		$couponValidCount = $couponClass->getCheckCouponValidDateCount($param);
		if($couponValidCount === false){
			throw new RollbackException('유효 데이터를 체크하는 중에 오류가 발생하였습니다.');
		}

		if ($couponValidCount < 1) {
			throw new RollbackException('유효 데이터가 존재하지 않습니다. 작업을 진행하지 않겠습니다.');
		}

		// 날짜가 지난 쿠폰 삭제
		$updateParam = [
			'is_del'=>'Y',
			'expiration_date'=> $today,
			'where_is_del'=> 'Y'
		];

		$updateCouponDeleteResult = $couponClass->updateCouponDelete($updateParam);
		if ($updateCouponDeleteResult < 1) {
			throw new RollbackException('유효기간이 만료된 쿠폰을 삭제하는 중에 오류가 발생했습니다.');
		}

		// 고객에 지급된 쿠폰 중에 사용내역이 없는 것만 삭제 
		$couponValidList = $couponClass->getCheckCouponValidDateList($param);
		if ($couponValidList === false) {
			throw new RollbackException('유효기간 지난 데이터를 찾으면서 오류가 발생했습니다.');
		}

		$couponValidCount = $couponValidList->recordCount();
		if ($couponValidCount > 0) {
			// 지급된 쿠폰이 있는 경우에는 삭제한다.
			$updateCouponMemberDeleteResult = $couponClass->updateCouponMemberDelete($couponValidList);
			if ($updateCouponDeleteResult < 1) {
				throw new RollbackException('지급 된 쿠폰 내역을 삭제 하다가 오류가 발생했습니다.');
			}
		}

		$alertMessage = '유효기간이 만료된 쿠폰 데이터가 삭제되었습니다. 감사합니다.';

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