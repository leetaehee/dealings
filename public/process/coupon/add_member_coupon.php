<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 회원에게 쿠폰 지급 
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

		$isCouponValid = $couponClass->getValidCouponIdx($couponIdx, $isUseForUpdate);
		if ($isCouponValid === false) {
			throw new RollbackException('쿠폰 키 값을 검사하는 중에 오류가 발생했습니다.');
		}

		$couponData = $couponClass->getMemberCouponData($couponIdx, $isUseForUpdate);
		if ($couponData === false) {
			throw new Exception('쿠폰 정보를 가져오는 중에 오류가 발생했습니다.');
		}

		$couponOverlapParam = [
			'coupon_idx'=> $couponIdx,
			'member_idx'=> $getData['member_idx'],
			'issue_type'=> $couponData->fields['issue_type'],
			'is_coupon_del'=> 'N',
			'is_del'=> 'N'
		];

		$overlapCount = $couponClass->getCheckCouponOverlapData($couponOverlapParam, $isUseForUpdate);
		if ($overlapCount === false) {
			throw new RollbackException('쿠폰 발행 중복검사 중에 오류가 발생했습니다.');
		}

		if ($overlapCount > 0) {
			throw new RollbackException('쿠폰이 이미 발행되어서 등록 할 수 없습니다.');
		}
		
		$couponStatusName = '사용대기';

		$couponStatusCode = $couponClass->getCouponStatusCode($couponStatusName, $isUseForUpdate);
		if ($couponStatusCode === false) {
			throw new RollbackException('쿠폰 상태 코드를 가져오면서 오류가 발생했습니다.');
		}

		if (empty($couponStatusCode)) {
			throw new RollbackException('쿠폰 상태 코드를 찾을 수 없습니다.');
		}

		$insertMemberData = [
			'issue_type'=> $couponData->fields['issue_type'],
			'coupon_idx'=> $couponData->fields['idx'],
			'sell_item_idx'=> $couponData->fields['sell_item_idx'],
			'member_idx'=> $getData['member_idx'],
			'subject'=> $couponData->fields['subject'],
			'discount_rate'=> $couponData->fields['discount_rate'],
			'item_money'=> $couponData->fields['item_money'],
			'coupon_status'=> $couponStatusCode
		];
		
		$insetMemberResult = $couponClass->insertCouponMember($insertMemberData);
		if ($insetMemberResult < 1){
			throw new RollbackException('회원 쿠폰정보에 데이터를 삽입중에 오류가 발생했습니다.');
		}

		$returnUrl = SITE_ADMIN_DOMAIN . '/coupon_member_status.php';
		$alertMessage = '정상적으로 지급이 되었습니다.';

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