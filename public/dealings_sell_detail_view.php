<?php
	/*
	 * 판매 거래 상세 화면 
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
		$title = TITLE_VOUCHER_SELL_DETAIL_VIEW . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN.'/voucher_purchase_list.php'; // 리턴되는 화면 URL 초기화
		$alertMessage = '';

		$actionUrl = DEALINGS_PROCESS_ACCTION . '/changeStatus.php';
		$JsTemplateUrl = JS_URL . '/dealings_sell_detail_view.js';
		$dealingsType = '판매';
		$btnName = '구매하기';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// xss, injection 방지
		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$_GET['type'] = htmlspecialchars($_GET['type']);
		$getData = $_GET;

		// 디비에서 거래상태받아오기, 거래타입과 키값 보내기
		$dealingsClass = new DealingsClass($db);

		$dealingsData = $dealingsClass->getDealingsData($getData['idx']);
		if ($dealingsData === false) {
			throw new Exception('회원 판매 거래정보 가져오는데 오류가 발생했습니다.');
		}

		$_SESSION['dealings_writer_idx'] = $dealingsData->fields['writer_idx'];
		$_SESSION['dealings_idx'] = $getData['idx'];
		$_SESSION['dealings_status'] = $getData['type'];

		// 거래상태 변경
		$DealingsStatusChangehref = $actionUrl . '?mode=change_status&dealings_idx ='.$getData['type'];

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/dealings_sell_detail_view.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_voucher.html.php'; // 전체 레이아웃