<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 마이페이지
	 */
	
	// 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	// 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	// 공통함수
	 include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	// 현재 세션체크
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php';
    
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php'; // adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php'; // adodb

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php'; // Class 파일

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_MY_ACCOUNT_SETTING . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화.
		$alertMessage = '';
		$actionUrl = MEMBER_PROCESS_ACTION . '/member_process.php'; // form 전송시 전달되는 URL.
		$actionMode = 'account'; 
		$JsTemplateUrl = JS_URL . '/account.js'; 
		$idx = $_SESSION['idx'];

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$memberClass = new MemberClass($db);

		$accountData = $memberClass->getAccountByMember($idx);
		if($accountData === false) {
			throw new Exception('회원정보를 찾을 수 없습니다.');
		}

		$accountNo = setDecrypt($accountData->fields['account_no']);
		$accountBank = $accountData->fields['account_bank'];

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/my_account.html.php';
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

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃