<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 
		1. 회원 정보 수정 
		2. (기능1) 회원 정보 수정
		3. (기능2) 회원 탈퇴
	 */

	 // 환경설정
	 include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	 // 메세지
	 include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	 // 공통함수
	 include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	 // PHP메일보내기
	 include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/mailer.lib.php';
	 // 현재 세션체크
	 include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php';
	 // PDO 객체 생성
	 include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/databaseConnection.php';

	 try {
		$returnUrl = '';
		$result = false;

		$mode = isset($_POST['mode']) ?  $_POST['mode'] : $_GET['mode'];

		if ($mode == 'modi') {
			// 회원정보수정
		} else if ($mode == 'del') {
			// 회원탈퇴 
			$idx = isset($_GET['idx']) ? $_GET['idx'] : '';
			$query = 'update `imi_members`
					   set `withdraw_date` = CURDATE(),
						   `modify_date` = CURDATE()
					   where `idx` = :idx';
			$stmt = $pdo->prepare($query);
			$stmt-a>bindValue(':idx',$idx);

			// 트랜잭션 추가 
			$pdo->beginTransaction();
			$result = $stmt->execute();
			$pdo->commit();

			 if ($result == true) {
				 // 세션제거
				 session_destroy();
				 // 처음화면이동
				 $returnUrl = SITE_DOMAIN;
			 } else {
				 $returnUrl = SITE_DOMAIN.'/mypage.php';
			 } 
			 alertMsg($returnUrl);
		}
	 } catch (Exception $e) {
		 $pdo->rollback();
		 $output = DB_CONNECTION_ERROR_MESSAGE.$e->getMessage().', 위치: '.$e->getFile().':'.$e->getLine();
		 echo $output;
	 }
