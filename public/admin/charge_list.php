<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 마일리지 결제내역
	 */
	
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php'; // 현재 세션체크
    
	// adodb
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';
	
	// Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MileageClass.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_ADMIN_CHARGE_STATUS . ' | ' . TITLE_ADMIN_SITE_NAME;
		
		// 리턴되는 화면 URL 초기화.
		$returnUrl = SITE_ADMIN_DOMAIN.'/admin_page.php';
		$alertMessage = '';
		$chargeCancelUrl = MILEAGE_PROCESS_ACTION.'/mileage_cancel.php'; // 취소처리
		$idx = $_SESSION['mIdx'];

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }
		
		$mileageClass = new MileageClass($db); 
		$chageList = $mileageClass->getChargeList();

		if($chageList === false){
			throw new Exception('충전내역을 가져오면서 오류가 발생했습니다.');
		}

		$rocordCount = $chageList->recordCount();

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/charge_list.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if ($connection == true) {
			$db->close();
		}

		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	}
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_main.html.php'; // 전체 레이아웃