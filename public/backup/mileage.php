<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 회원 마일리지 현황 
	 */

	// 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	// 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	// 공용함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	// 현재 세션체크
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php';
	// PDO 객체 생성
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/databaseConnection.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_MILEAGES_MENU. ' | ' . TITLE_SITE_NAME;

		// form 전송시 전달되는 URL.
		$actionUrl = MILEAGE_PROCESS_ACTION. '/mileage_process.php';
		
		// 쿼리 실행결과
		$result = 0;

		$query = 'SELECT `imc`.`charge_cost`,
						 `imc`.`spare_cost`,
						 `imc`.`charge_status`,
						 `imc`.`charge_date`,
						 `im`.`idx`,
						 `im`.`id`,
						 `im`.`name`,
						 `imcd`.`mileage_name`
				  FROM `imi_mileage_charge` `imc`  
					LEFT JOIN `imi_members` `im`
						ON `imc`.`member_idx` = `im`.`idx`
					LEFT JOIN `imi_mileage_code` `imcd`
						ON `imc`.`charge_status` = `imcd`.`mileage_code`
				  WHERE `im`.`withdraw_date` IS NULL
				  AND `im`.`is_forcedEviction` = \'N\'
				  ORDER BY `im`.`name` ASC, `imc`.`charge_date` DESC
				 ';
		$stmt = $pdo->prepare($query);
		$result = $stmt->execute();  // 실행
		
		if ($result > 0) {
			$members = $stmt->fetchAll(); // 회원데이터 
		}

		ob_Start();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/mileage.html.php'; // 템플릿
		$output = ob_get_clean();
	} catch(Exception $e) {
		$output = DB_CONNECTION_ERROR_MESSAGE.$e->getMessage().', 위치: '.$e->getFile().':'.$e->getLine();
	}

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout.html.php'; // 전체 레이아웃
