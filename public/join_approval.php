<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 회원가입 메일 승인 화면 
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php'; // adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php'; // adodb

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php'; // Class 파일

	// 템플릿에서 <title>에 보여줄 메세지 설정
	$title = TITLE_JOIN_APPROVAL . ' | ' . TITLE_SITE_NAME;

	$idx = isset($_GET['idx']) ? $_GET['idx'] : $_POST['idx'];  
	
	if (!empty($idx)) {
		$returnUrl = SITE_DOMAIN.'/login.php';

		$memberClass = new MemberClass($db);
		
		$db->beginTrans();
		$join_approval_date = $memberClass->getJoinApprovalMailDate($idx);

		if($join_approval_date==true){
			alertMsg(SITE_DOMAIN,1,'이미 가입승인 메일을 통해 승인 하였습니다!');
		}else{	
			$updateApprovalResult = $memberClass->updateJoinApprovalMailDate($idx);

			if ($updateApprovalResult < 1) {
				$db->rollbackTrans();
				alertMsh(SITE_DOMAIN,1,'오류! 관리자에게 문의하세요');
			}
		}
		$db->commitTrans();
	} else {
		alertMsg(SITE_DOMAIN,'1','잘못된 접근입니다!');
	}
	
	ob_Start();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/join_approval.html.php'; // 템플릿
	$output = ob_get_clean();

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout.html.php'; // 전체 레이아웃
