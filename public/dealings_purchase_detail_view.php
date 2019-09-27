<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 구매 거래 상세 화면 
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
		$title = TITLE_VOUCHER_PURCHASE_DETAIL_VIEW . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN.'/voucher_purchase_list.php'; // 리턴되는 화면 URL 초기화
		$alertMessage = '';

		$actionUrl = DEALINGS_PROCESS_ACCTION . '/changeSellStatus.php';
		$JsTemplateUrl = JS_URL . '/dealings_purchase_detail_view.js';
		$dealingsType = '구매';
		$btnName = '판매하기';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// xss, injection 방지
		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$_GET['type'] = htmlspecialchars($_GET['type']);
		$getData = $_GET;

		// 디비에서 거래상태받아오기, 거래타입과 키값 보내기
		$dealingsClass = new DealingsClass($db);
		$couponClass = new CouponClass($db);

		$dealingsData = $dealingsClass->getDealingsData($getData['idx']);
		if ($dealingsData === false) {
			throw new Exception('회원 구매 거래정보 가져오는데 오류가 발생했습니다.');
		}

		$itemMoney = $dealingsData->fields['item_money'];
        $itemNo = $dealingsData->fields['item_no'];
		$dealingsMileage = $dealingsData->fields['dealings_mileage'];

		$_SESSION['dealings_writer_idx'] = $dealingsData->fields['writer_idx'];
		$_SESSION['dealings_idx'] = $getData['idx'];
		$_SESSION['dealings_status'] = $getData['type'];
		$_SESSION['dealings_mileage'] = $dealingsMileage;

		// 이용가능한 쿠폰 가져오기 
		$couponParam = [
            'itemNo'=> $itemNo,
			'issue_type'=> '판매',
			'item_money'=> $itemMoney,
			'is_del'=> 'N',
			'start_date'=> date('Y-m-d'),
			'end_date'=> date('Y-m-d'),
            'member_idx'=> $_SESSION['idx'],
			'is_refund'=> 'N'
		];
		
		// 사용가능한 쿠폰 리스트 가져오기
		$couponData = $couponClass->getAvailableCouponData($couponParam);
		if ($couponData === false) {
			throw new Exception('사용가능한 쿠폰을 가져 올 수 없습니다. 가져 올 수 없습니다');
		} else {
			$couponDataCount = $couponData->recordCount();
		}

		// 거래상태 변경
		$DealingsStatusChangehref = $actionUrl . '?mode=change_status&dealings_idx ='.$getData['idx'];

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/dealings_purchase_detail_view.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_voucher.html.php'; // 전체 레이아웃