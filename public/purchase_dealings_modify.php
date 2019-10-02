<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 구매 거래 수정화면
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

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_VOUCHER_PURCHASE_MODIFY . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN.'/voucher_dealings.php'; // 리턴되는 화면 URL 초기화
		$alertMessage = '';

		$actionUrl = DEALINGS_PROCESS_ACCTION . '/dealings_modify.php'; // form action url
		$JsTemplateUrl = JS_URL . '/voucher_purchase_enroll.js';

		$dealingsIdx = $_GET['idx'];

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$dealingsClass = new DealingsClass($db);

		$voucherList = $dealingsClass->getVoucherList();
		if ($voucherList == false) {
			throw new Exception('상품권 정보를 가져오다가 오류 발생! 관리자에게 문의하세요.');
		} else {
			$vourcherListCount = $voucherList->recordCount();
		}

		$dealingsData = $dealingsClass->getDealingsData($dealingsIdx);
		if ($dealingsData === false) {
			throw new Exception('회원 구매 거래정보를 가져 올 수 없습니다.');
		}

		$itemNo = $dealingsData->fields['item_no'];
		$itemMoney = $dealingsData->fields['item_money'];

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/purchase_dealings_modify.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃