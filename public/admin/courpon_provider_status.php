<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 쿠폰 발행하기
	 */
	
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; //메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php'; // 세션체크
	
	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	// Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/CouponClass.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_COUPON_PROVIDER_STATUS . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN.'/coupon.php'; // 리턴되는 화면 URL 초기화
		$btnName = '';

		$alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$getData = $_GET;

		$memberIdx = $getData['idx'];

		$couponClass = new CouponClass($db);

		$providerParam = [
			'is_refund'=> 'N',
			'member_idx'=> $memberIdx,
			'is_del'=> 'N'
		];

		$couponProviderList = $couponClass->getCouponProvierStatus($providerParam);
		if ($couponProviderList === false) {
			throw new Exception('회원 쿠폰 데이터를 가져오는 중에 오류가 발생했습니다.');
		}

		$couponProviderListCount = $couponProviderList->recordCount();

		// 발급한 쿠폰삭제 URL
		$couponDeleteURL = COUPON_PROCEE_ACTION . '/delete_coupon.php';
		// 발급한 쿠폰수정 URL 
		$couponUpdateURL =  SITE_ADMIN_DOMAIN . '/member_coupon_update.php';

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/coupon_provider_status.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_coupon.html.php';// 전체 레이아웃