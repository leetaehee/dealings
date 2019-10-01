<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 판매 거래 등록 화면
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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/DealingsClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/CouponClass.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_VOUCHER_SELL_ENROLL . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN.'/voucher_dealings.php'; // 리턴되는 화면 URL 초기화
		$alertMessage = '';

		$actionUrl = DEALINGS_PROCESS_ACCTION . '/sell_enroll.php'; // form action url
		$JsTemplateUrl = JS_URL . '/voucher_sell_enroll.js';
		$dealingsState = '거래대기';
		$dealingsType = '판매';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$dealingsClass = new DealingsClass($db);
		$couponClass = new CouponClass($db);

		$voucherList = $dealingsClass->getVoucherList();
		if ($voucherList == false) {
			throw new Exception('상품권 정보를 가져오다가 오류 발생! 관리자에게 문의하세요.');
		} else {
			$vourcherListCount = $voucherList->recordCount();
		}

		// 이용가능한 쿠폰 가져오기 
		$couponParam = [
			'issue_type'=> '판매',
			'is_coupon_del'=> 'N',
			'is_del'=> 'N',
            'member_idx'=> $_SESSION['idx'],
			'is_refund'=> 'N'
		];
		
		// 사용가능한 쿠폰 리스트 가져오기
		$couponData = $couponClass->getAvailableAllCouponData($couponParam);
		if ($couponData === false) {
			throw new Exception('사용가능한 쿠폰을 가져 올 수 없습니다. 가져 올 수 없습니다');
		} else {
			$couponDataCount = $couponData->recordCount();
		}


		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/voucher_sell_enroll.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_voucher.html.php'; // 전체 레이아웃