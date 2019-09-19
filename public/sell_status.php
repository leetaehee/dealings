<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 판매 거래 목록
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
		$title = TITLE_VOUCHER_SELL_STATUS . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN.'/voucher_dealings.php'; // 리턴되는 화면 URL 초기화
		$alertMessage = '';
		$dealingsType = '판매';

		$dealingsClass = new DealingsClass($db);
		
		$purchaseParam = [
			'dealings_member_idx'=>$_SESSION['idx'],
			'dealings_type'=>$dealingsType,
			'dealings_status'=>1,
			'is_del'=>'N'
		];

		$sellList = $dealingsClass->getDealingIngList($purchaseParam);
		if ($sellList === false) {
			throw new Exception('판매현황 데이터를 가져오면서 오류 발생! 관리자에게 문의하세요');
		} else {
			$sellListCount = $sellList->recordCount();
		}

		// 거래상세화면 이동
		$DealingsDetailViewHref = SITE_DOMAIN . '/dealings_sell_status_view.php?';

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/sell_status.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_voucher.html.php'; // 전체 레이아웃