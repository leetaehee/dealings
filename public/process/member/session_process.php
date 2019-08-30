<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 
		1. 로그인 기능
		2. 로그인 시 관리자 유무/탈퇴자/강제탈퇴자 구분하기
		3. 강제탈퇴 된 사용자는 로그인페이지로 보내기.
		4. 로그인이 정보가 틀린 사람은 로그인 페이지로 이동
		5. 세션 추가 사항-  아이디, 관리자 유무, 등급, 이름, 
		6. 확인해야 하는 정보- 강제탈퇴유무, 메일 승인여부, 탈퇴유무
	 */

	 // 환경설정
	 include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	 // 공통함수
	 include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	 // PDO 객체 생성
	 include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/databaseConnection.php';

	 try {
		 $returnUrl = ''; // 리턴되는 화면 URL 초기화.
		 $result = false;
		 $mode = isset($_POST['mode']) ?  $_POST['mode'] : $_GET['mode'];

		 if ($mode == 'login') {
			 // 유효성 검증 및 로그인실패시 이동 링크
			 $returnUrl = SITE_DOMAIN.'/login.php'; 
			 $postData = [
				'mode'=>$mode,
				'id'=>isset($_POST['id']) ? $_POST['id'] : '',
				'password'=>isset($_POST['password']) ? $_POST['password'] : ''
			 ];

			 // 로그인 유효성 검증
			 if (checkMemberFormValidate($_POST) == true) {
				 $query = 'SELECT `imi`.`id`,
								  `imi`.`idx`,
								  `imi`.`password`,
								  `imi`.`name`,
								  `imi`.`grade_code`,
								  `imi`.`is_forcedEviction`,
								  `imi`.`forcedEviction_date`,
								  `imi`.`join_approval_date`,
								  `imi`.`withdraw_date`,
								  `imi`.`admin_grade`,
								  `img`.`grade_code`,
								  `img`.`grade_name`,
								  `img`.`member_type`,
								  `img2`.`member_type` `admin_type`,
								  `img2`.`grade_name` `admin_grade_name`
							FROM `imi_members` `imi`
								LEFT JOIN `imi_member_grades` `img`
									ON `imi`.`grade_code` = `img`.`grade_code`
									AND `img`.`member_type` = :member_type
								LEFT JOIN `imi_member_grades` `img2`
									ON `imi`.`admin_grade` = `img2`.`grade_code`
									AND `img2`.`member_type` = :admin_type
							WHERE `imi`.`id` = :id
							AND `imi`.`join_approval_date` IS NOT NULL
							AND `imi`.`withdraw_date` IS NULL
							AND `imi`.`is_forcedEviction` = "N"
						';

				 $stmt = $pdo->prepare($query);
				 $stmt->bindValue(':member_type', 'member');
				 $stmt->bindValue(':admin_type', 'admin');
				 $stmt->bindValue(':id', $_POST['id']);
				 
				 $result = $stmt->execute();
				 
				 if ($result == true) {
					 $account = $stmt->fetch();
					 
					 if (password_verify($postData['password'], $account['password'])) {
						 $_SESSION['idx'] = $account['idx'];
						 $_SESSION['id'] = $account['id'];
						 $_SESSION['name'] = setDecrypt($account['name']);
						 $_SESSION['grade_code'] = $account['grade_code'];
						 $_SESSION['admin_grade'] = $account['admin_grade'];
						 $_SESSION['member_type'] = $account['member_type'];
						 $_SESSION['admin_grade_name'] = $account['admin_grade_name'];
						 $_SESSION['admin_type'] = $account['admin_type'];

						 $returnUrl = SITE_DOMAIN.'/index.php'; // 로그인 성공 시 이동 링크
					 }
				 }
			 }
			 alertMsg($returnUrl);
		 }
	 } catch (Exception $e) {
		$output = DB_CONNECTION_ERROR_MESSAGE.$e->getMessage().', 위치: '.$e->getFile().':'.$e->getLine();
		echo $output;
	 }