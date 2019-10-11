<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 쿠폰수정
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수

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

		if ($connection === false) {
           throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$couponClass = new CouponClass($db);

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

		$memberParam = [
			'idx'=> $idx,
			'is_del'=> 'N'
		];

		$couponMemberIdx = $couponClass->getCouponMemberIdx($memberParam, $isUseForUpdate);
		if ($couponMemberIdx === false) {
			throw new RollbackException('지급된 쿠폰의 고유키를 가져오면서 오류가 발생했습니다.'); 
		}

		if ($couponMemberIdx == ''){
			throw new RollbackException('지급 된 쿠폰의 회원 고유키를 찾지 못했습니다.');
		}

		$existMemberParam = [
			'coupon_idx'=> $couponIdx,
			'member_idx' => $_SESSION['mIdx'],
			'is_del'=> 'N'
		];

		$isExistMemberIdx = $couponClass->getExistCouponMemberIdx($existMemberParam, $isUseForUpdate);
		if ($isExistMemberIdx === false) {
			throw new RollbackException('쿠폰이 지급되어있는지 확인인하면서 오류가 발생했습니다.');
		}
		
		if (!empty($isExistMemberIdx)) {
			throw new RollbackException('쿠폰이 이미 등록되어있습니다. 다시 등록하세요.');
		}

		$couponData = $couponClass->getMemberCouponData($couponIdx, $isUseForUpdate);
		if ($couponData === false) {
			throw new RollbackException('쿠폰 정보를 가져오는 중에 오류가 발생했습니다.');
		}

		$updateCouponMemberData = [
			'issue_type'=> $couponData->fields['issue_type'],
			'coupon_idx'=> $couponData->fields['idx'],
			'sell_item_idx'=> $couponData->fields['sell_item_idx'],
			'subject'=> $couponData->fields['subject'],
			'discount_rate'=> $couponData->fields['discount_rate'],
			'item_money'=> $couponData->fields['item_money'],
			'is_del'=> $couponData->fields['is_del'],
			'coupon_member_idx'=>$idx
		];

		$updateCouponMemberResult = $couponClass->updateCouponMember($updateCouponMemberData);
		if ($updateCouponMemberResult < 1) {
			throw new RollbackException('쿠폰 지급 정보를 수정하면서 오류가 발생했습니다.');
		}
		
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