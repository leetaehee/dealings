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

	 $top_dir = '/../../..';

	 include __DIR__.$top_dir.'/configs/config.php'; // 환경설정
	 include __DIR__.$top_dir.'/includes/function.php'; // 공통함수

	 $return_url = ''; // 리턴되는 화면 URL 초기화.
	 $is_valid_falid = true; // 유효성 검증 실패시에 체크하는 변수

	 if($_POST['mode']=='login'){
		 // 유효성검증 실패시, 리턴 UTL
		 $return_url = SITE_DOMAIN.'/login.php';

		 // 로그인 유효성 검증
		 if(check_memberForm_validate($_POST)==false){
			$is_valid_falid = false;
		 }
		 
		 try {
			 include __DIR__.$top_dir.'/includes/databaseConnection.php'; // PDO 객체 생성

			 // DB에서 항목가져오기
			 $stmt = $pdo->prepare('
							SELECT `id`,
								   `idx`,
								   `password`,
								   `name`,
								   `grade_code`,
								   `is_admin`,
								   `is_superadmin`,
								   `is_forcedEviction`,
								   `forcedEviction_date`,
								   `join_approval_date`
								   `withdraw_date`
							FROM `imi_members` 
							WHERE `id` = :id
							AND `join_approval_date` IS NOT NULL
							AND `withdraw_date` IS NULL
							AND `is_forcedEviction` = "N"
						');
			 $stmt->bindValue(':id',$_POST['id']);
			 $stmt->execute();
			 $account = $stmt->fetch();
		 } catch (Exception $e){
			$output = DB_CONNECTION_ERROR_MESSAGE.$e->getMessage().', 위치: '.$e->getFile().':'.$e->getLine();
			echo $output;
		 }

		 // 검증  
		 if(password_verify($_POST['password'],$account['password']) && $is_valid_falid==true){
			 // 세션처리 후 index.php로 이동 
			 $_SESSION['idx'] = $account['idx'];
			 $_SESSION['id'] = $account['id'];
			 $_SESSION['name'] = $account['name'];
			 $_SESSION['group_code'] = $account['grade_code'];
			 $_SESSION['is_admin'] = $account['is_admin'];
			 $_SESSION['is_superadmin'] = $account['is_superadmin'];
			 
			 header('location: '.SITE_DOMAIN);
		 }else{
			 // 실패시 로그인 페이지로 리턴 
			 header('location: '.$return_url);
		 }
	 }