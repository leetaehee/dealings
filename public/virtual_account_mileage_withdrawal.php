<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 가상계좌 출금 등록 화면
	 */
	
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php'; // 현재 세션체크
    
	// adodb
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';
	
	// Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MileageClass.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_VIRTUAL_MILEGE_WITHDRAWAL . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화
		$alertMessage = '';

		$actionUrl = MILEAGE_PROCESS_ACTION . '/virtual_account_withdrawal.php'; // form action url
		$JsTemplateUrl = JS_URL . '/virtual_account_withdrawal.js'; 

		$idx = $_SESSION['idx'];
		$mileageType = 5;
		
		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$memberClass = new MemberClass($db);
		$mileageClass = new MileageClass($db);

		$accountData = $memberClass->getAccountByMember($idx);
		if ($accountData === false) {
			throw new Exception('회원 계좌정보 가져오는데 오류 발생! 관리자에게 문의하세요!');
		}

		if ($accountData->fields['account_no']==null) {
			$returnUrl = $returnUrl.'/mypage.php';
			throw new Exception('출금계좌가 설정되어있지않습니다. 마이룸 > 내정보조회 > 출금계좌설정에서 설정하세요!');
		}

		$accountNo = $accountData->fields['account_no'];
		$accountBank = $accountData->fields['account_bank'];

		$mileageTypeParam = [
			'mileageType'=>$mileageType,
			'idx'=>$idx
		];

		$maxMileage = $mileageClass->getAvailableMileage($mileageTypeParam);
		if ($maxMileage < 0) {
			throw new Exception('마일리지 조회 오류! 관리자에게 문의하세요.');
		}

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/virtual_account_mileage_withdrawal.html.php';
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

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃