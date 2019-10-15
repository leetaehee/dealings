<?php
	/**
	 * 쿠폰 발행하기
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php';

	// Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/CouponClass.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_COUPON_ISSUE . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN.'/coupon.php'; // 리턴되는 화면 URL 초기화
		$actionUrl = COUPON_PROCEE_ACTION . '/issue_coupon.php';
		$JsTemplateUrl = JS_ADMIN_URL . '/issue_coupon.js';
		$btnName = '발행하기';

		$alertMessage = '';

		$voucherCount = count($CONFIG_COUPON_VOUCHER_ARRAY);
		$voucherMoneyCount = count($CONFIG_VOUCHER_MONEY_ARRAY);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/issue_coupon.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_coupon.html.php';// 전체 레이아웃