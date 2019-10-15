<?php
	/**
	 * 지급된 쿠폰을 수정할 수 있는 화면.
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
		$title = TITLE_COUPON_MODIFY . ' | ' . TITLE_ADMIN_SITE_NAME;

		$returnUrl = SITE_ADMIN_DOMAIN.'/coupon.php'; // 리턴되는 화면 URL 초기화
        $alertMessage = '';

		if ($connection === false) {
           throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$couponClass = new CouponClass($db);

		// injection, xss 방지코드
		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$_GET['coupon_idx'] = htmlspecialchars($_GET['coupon_idx']);
		$_GET['member_idx'] = htmlspecialchars($_GET['member_idx']);
		$getData = $_GET;

		$idx = $getData['idx'];

		// returnURL
		$returnUrl = SITE_ADMIN_DOMAIN . '/coupon.php';

		if (empty($idx)) {
			throw new Exception('비정상적인 접근입니다.'); 
		}

		$memberParam = [
			'idx'=> $idx,
			'is_del'=> 'N'
		];

		$isDelete = $couponClass->getCheckCouponMemeberDelete($idx);
		if ($isDelete === false) {
			throw new RollbackException('지급 된 쿠폰의 삭제 여부를 가져오다가 오류가 발생했습니다.');
		}

		$couponMemberIdx = $couponClass->getCouponMemberIdx($memberParam);
		if ($couponMemberIdx === false) {
			throw new RollbackException('지급된 쿠폰의 고유키를 가져오면서 오류가 발생했습니다.'); 
		}

		if ($isDelete == 'Y') {
			throw new RollbackException('지급 된 쿠폰은 이미 삭제 되었습니다.');
		}

		if ($couponMemberIdx == ''){
			throw new RollbackException('지급 된 쿠폰의 회원 고유키를 찾지 못했습니다.');
		}

		$couponUseParam = [
			'coupon_member_idx'=> $idx,
			'coupon_idx'=> $getData['coupon_idx'],
			'is_refund'=> 'N'
		];

		$memberCouponIdx = $couponClass->getCheckIsUseCouponByMember($couponUseParam);
		if ($memberCouponIdx === false) {
			throw new RollbackException('지급된 쿠폰의 사용내역을 가져오면서 오류가 발생했습니다.');
		}

		if(!empty($memberCouponIdx)) {
			throw new RollbackException('쿠폰 사용내역이 존재하여 변경 할 수 없습니다.');
		}

		$couponParam = [
			'member_idx'=> $_SESSION['mIdx'],
			'is_del'=> 'N',
			'is_coupon_del'=> 'N'
		];

		$couponList = $couponClass->getMemberAvailableCouponList($couponParam);
		if ($couponList === false) {
			throw new Exception('회원 쿠폰 데이터를 가져오는 중에 오류가 발생했습니다.');
		}

		$couponListCount = $couponList->recordCount();

		// 쿠폰 변경을 처리하는 PROCESS URL
		$couponUpdateURL = COUPON_PROCEE_ACTION . '/update_coupon.php';

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/member_coupon_update.html.php';
	} catch (Exception $e) {
		// 트랜잭션을 사용하지 않을 때
		$alertMessage = $e->getMessage();
    } finally {
        if  ($connection === true) {
            $db->close();
        }
        if (!empty($alertMessage)) {
            alertMsg($returnUrl, 1, $alertMessage);
        }
    }
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_coupon.html.php';// 전체 레이아웃