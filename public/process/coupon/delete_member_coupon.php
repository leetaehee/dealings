<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 지급된 쿠폰을 삭제
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
		$getData = $_GET;

		// returnURL
		$returnUrl = SITE_ADMIN_DOMAIN . '/coupon.php';

		if (empty($getData['idx'])) {
			throw new Exception('비정상적인 접근입니다.'); 
		}

		$db->startTrans();

		$memberParam = [
			'idx'=> $getData['idx'],
			'is_del'=> 'N'
		];		
		
		$couponMemberIdx = $couponClass->getCouponMemberIdx($memberParam);
		if ($couponMemberIdx === false) {
			throw new RollbackException('지급된 쿠폰의 고유키를 가져오면서 오류가 발생했습니다.'); 
		}

		if ($couponMemberIdx == ''){
			throw new RollbackException('지급된 쿠폰의 회원 고유키를 찾지 못했습니다.');
		}


		$memberCouponIdx = $couponClass->getCheckIsUseCouponByMember($getData['idx']);
		if ($memberCouponIdx === false) {
			throw new RollbackException('지급된 쿠폰의 사용내역을 가져오면서 오류가 발생했습니다.');
		}

		if(!empty($memberCouponIdx)) {
			throw new RollbackException('쿠폰 사용내역이 존재하여 삭제 할 수 없습니다.');
		}

		$deleteParam = [
			'is_del'=> 'Y',
			'idx'=> $getData['idx']
		];

		$deleteCouponResult = $couponClass->deleteCouponMember($deleteParam);
		if ($deleteCouponResult < 1) {
			throw new RollbackException('지급된 쿠폰을 삭제하다가 오류가 발생하였습니다.');
		}

		$returnUrl = SITE_ADMIN_DOMAIN . '/courpon_privider_status.php?idx=' . $getData['idx'];
		$alertMessage = '지급된 쿠폰이 정상적으로 삭제되었습니다.';

		$db->commitTrans();
		$db->completeTrans();
	} catch (RollbackException $e) {
		// 트랜잭션 문제가 발생했을 때
		$alertMessage = $e->errorMessage();
		$db->rollbackTrans();
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