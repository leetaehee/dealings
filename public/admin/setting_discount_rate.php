<?php
	/**
	 * 쿠폰 할인율 제한 설정
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php';
	
	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	// Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/CouponClass.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_COUPON_MAXIMUM_DISCOUNT_RATE . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN.'/coupon.php'; // 리턴되는 화면 URL 초기화
		$actionUrl = COUPON_PROCEE_ACTION . '/setting_discount_rate.php';
		$JsTemplateUrl = JS_ADMIN_URL . '/setting_discount_rate.js';
		$btnName = '설정하기';

		$alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$voucherCount = count($CONFIG_COUPON_VOUCHER_ARRAY);
		$voucherMoneyCount = count($CONFIG_VOUCHER_MONEY_ARRAY);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/setting_discount_rate.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if ($connection === true) {
			$db->close();
		}

		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_coupon.html.php';// 전체 레이아웃