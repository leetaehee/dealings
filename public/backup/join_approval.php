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
	
	// for update
	
	if (isset($_GET['idx'])) {
		$memberClass = new MemberClass($db);

		// 한번 승인 한 경우 로그인 페이지로 이동.
		$query = 'SELECT `join_approval_date` 
				  FROM `imi_members` 
				  WHERE `idx` = :idx';
		$stmt = $pdo->prepare($query);
		$stmt->bindValue(':idx', $_GET['idx']);
		$stmt->execute();
		$idResult = $stmt->fetch();	

		if($idResult['join_approval_date'] != null){
			// 메인승인한 경우 로그인페이지로 이동
			alertMsg(SITE_DOMAIN.'/login.php');
		}

		// 승인일자에 현재일자로 업데이트 하여 정상적인 활동이 가능하도록 한다.
		$query = 'UPDATE `imi_members`
				  SET `join_approval_date` = CURDATE()
				  WHERE `idx` = :idx'; 
		$stmt = $pdo->prepare($query);
		$stmt->bindValue(':idx', $_GET['idx']);

		// 트랜잭션 추가
		$pdo->beginTransaction();
		$result = $stmt->execute();
		$pdo->commit();

		if ($result == false) {
			echo '오류!';
		}
	} else {
		alertMsg(SITE_DOMAIN,'1','잘못된 접근입니다!');
	}
	
	ob_Start();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/join_approval.html.php'; // 템플릿
	$output = ob_get_clean();


	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout.html.php'; // 전체 레이아웃
