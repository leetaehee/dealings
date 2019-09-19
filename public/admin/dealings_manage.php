<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 나의 구매글 등록 현황
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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/DealingsClass.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_ADMIN_DEALINGS_STATUS . ' | ' . SITE_ADMIN_DOMAIN;
		$returnUrl = SITE_ADMIN_DOMAIN.'/admin_page.php'; // 리턴되는 화면 URL 초기화
		$actionUrl = DEALINGS_PROCESS_ACCTION . '/dealings_process.php';
		$alertMessage = '';
		$dealingsType = '구매';

		$dealingsClass = new DealingsClass($db);

		$payDealingsList = $dealingsClass->getPayCompletedDealingIngList();
		if ($payDealingsList === false) {
			throw new Exception('결제 완료 된 데이터를 가져올 수 없습니다! 관리자에게 문의하세요');
		} else {
			$payDealingsListCount = $payDealingsList->recordCount();
		}

		// 거래완료 및 환불 링크
		$DealingsDetailViewHref = $actionUrl . '?mode=finish_dealings';

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/dealings_manage.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_main.html.php';// 전체 레이아웃