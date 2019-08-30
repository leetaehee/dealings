<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 
		1. 관리자 페이지
		2. (기능1)관리자 부여 기능 
		3. (기능2) 회원 강제 탈퇴 기능 
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

		if ($mode == 'setting_manager' || $mode == 'setting_forcedEviction') {
			$returnUrl = SITE_DOMAIN.'/admin.php';
			// 멤버 등급 변동 시 사유 컬럼
			$changeMsg = '';

			$value = isset($_GET['value']) ? $_GET['value'] : '';
			$idx = isset($_GET['idx']) ? $_GET['idx'] : '';
			
			if ($mode == 'setting_manager') {
				$changeMsg = ($value == '1') ? '일반관리자로 설정.' : '회원으로 변경';
				// 관리자설정
				$query = 'UPDATE `imi_members`
						  SET `admin_grade` = :admin_grade
						  WHERE `idx` = :idx
						 ';
				$stmt = $pdo->prepare($query);
				$stmt->bindValue(':admin_grade',$value);
				$stmt->bindValue(':idx',$idx);
			} else if($mode == 'setting_forcedEviction') {
				// 강제탈퇴 처리
				if ($value == 'Y') {
					$changeMsg = '강제탈퇴 설정.';
					$query = 'UPDATE `imi_members`
							   SET `is_forcedEviction` = :is_forcedEviction,
								   `forcedEviction_date` = CURDATE()
							   WHERE `idx` = :idx';
				} else {
					$changeMsg = '강제탈퇴 해제.';
					$query = 'UPDATE `imi_members`
							   SET `is_forcedEviction` = :is_forcedEviction,
								   `forcedEviction_date` = NULL
							   WHERE `idx` = :idx
							';				
				}
				$stmt = $pdo->prepare($query);
				$stmt->bindValue(':is_forcedEviction',$value);
				$stmt->bindValue(':idx',$idx);
			}

			$historyParam = [
					'idx'=>$idx,
					'changeMsg'=>$changeMsg
				];

			// 트랜잭션 추가 
			$pdo->beginTransaction();
			$updateresult = $stmt->execute();

			if ($updateresult > 0) {
				$result = insertActivityHistory($pdo, $historyParam);
			}

			$pdo->commit();

			if ($result == true) {
				alertMsg($returnUrl);
			} else {
				alertMsg(SITE_DOMAIN);
			}
		 }
	 } catch (Exception $e) {
		$pdo->rollback();
		$output = DB_CONNECTION_ERROR_MESSAGE.$e->getMessage().', 위치: '.$e->getFile().':'.$e->getLine();
		echo $output;
	 }