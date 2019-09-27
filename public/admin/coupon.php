<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 쿠폰관리 메인화면 
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공용함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php'; // 현재 세션체크

	try { 
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_COUPON_MAIN . ' | ' . TITLE_ADMIN_SITE_NAME;
		$alertMessage = '';

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/coupon.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	}

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_coupon.html.php'; // 전체 레이아웃