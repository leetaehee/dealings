<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 회원이 사용 가능한 쿠폰 조회 기능
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php';
	
	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	// Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/CouponClass.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_MY_COUPON_LIST . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN.'/voucher_dealings.php'; // 리턴되는 화면 URL 초기화

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$memberIdx = $_SESSION['idx'];

		$couponClass = new CouponClass($db);

		$myCouponParam = [
			'is_refund'=> 'N',
			'is_coupon_del'=> 'N',
			'is_del'=>'N',
			'member_idx'=> $memberIdx
		];

		$memberUseCouponList = $couponClass->getMemberUseAvailableCouponList($myCouponParam);
		if ($memberUseCouponList === false) {
			throw new Exception('회원 사용 가능 쿠폰 리스트를 가져오면서 오류가 발생했습니다.');
		}

		$memberUseCouponListCount = $memberUseCouponList->recordCount();

		// 거래상세화면 이동
		$DealingsDetailViewHref = SITE_DOMAIN . '/my_purchase_dealings_status.php?';

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/my_coupon_list.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_voucher.html.php'; // 전체 레이아웃