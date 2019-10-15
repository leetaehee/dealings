<?php
	/**
	 * 판매 거래 목록
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
		$title = TITLE_VOUCHER_PURCHASE_LIST . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN.'/voucher_dealings.php'; // 리턴되는 화면 URL 초기화
		$alertMessage = '';

		$dealingsState = 1;
		$dealingsType = '판매';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$dealingsClass = new DealingsClass($db);

		$param = [
			'dealingsType'=> $dealingsType,
			'dealingsState'=> $dealingsState,
			'is_del'=> 'N'
		];
		$dealingsList = $dealingsClass->getDealingsList($param);
		if ($dealingsList === false) {
			throw new Exception('거래 데이터 가져오는 중에 오류 발생! 관리자에게 문의하세요.');
		} else {
			$dealingsListCount = $dealingsList->recordCount();
		}

		// 거래상세화면 이동
		$DealingsDetailViewHref = SITE_DOMAIN . '/dealings_sell_detail_view.php?type=' . $dealingsState;

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/voucher_sell_list.html.php';
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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_voucher.html.php'; // 전체 레이아웃