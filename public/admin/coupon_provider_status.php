<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 쿠폰 지급 해야 할 회원 리스트
	 */
	
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; //메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php'; // 세션체크
	
	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	// Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_COUPON_ISSUE_MEMBER . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN.'/coupon.php'; // 리턴되는 화면 URL 초기화
		$alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$memberClass = new MemberClass($db);

		$memberList = $memberClass->getActivityMemberList();
		if ($memberList === false) {
			throw new Exception('지급 대상 리스트를 가져오면서 오류가 발생했습니다.');
		}
		$rocordCount = $memberList->recordCount();

		// 쿠폰 발급해주는 URL
		$couponProvideURL = SITE_ADMIN_DOMAIN . '/coupon_provider.php'; 
		// 쿠폰 발급 현황 URL
		$couponProvideStatusURL = SITE_ADMIN_DOMAIN . '/courpon_provider_status.php';

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/coupon_provider_status.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_coupon.html.php';// 전체 레이아웃