<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 관리자페이지 
	 */

	include __DIR__.'/../configs/config.php'; // 환경설정
	include __DIR__.'/../messages/message.php'; // 메세지
	include __DIR__.'/../includes/session_check.php'; // 현재 세션체크

	try {
        include __DIR__.'/../includes/databaseConnection.php'; // PDO 객체 생성
		$title = TITLE_ADMIN_MENU.' | '.TITLE_SITE_NAME; // 템플릿에서 <title>에 보여줄 메세지 설정

		$action_url = SITE_DOMAIN.'/admin.php'; // form 전송시 전달되는 URL.

		$search_name = '';
		if(isset($_POST['search_name'])){
			$search_name = $_POST['search_name'];
		}

		$search = '';
		if($_SESSION['is_superadmin']=='N'){
			$search = ' AND `im`.`is_superadmin` = :is_superadmin';
		}

		if(!empty($search_name)){
			$search .= ' AND `im`.`name` = :name';
		}

		$query = 'SELECT `im`.`idx`,
						 `im`.`id`,
						 `im`.`grade_code`,
						 `im`.`email`,
						 `im`.`name`,
						 `im`.`phone`,
						 `im`.`sex`,
						 `im`.`birth`,
						 `im`.`join_date`,
						 `im`.`join_approval_date`,
						 `im`.`withdraw_date`,
						 `im`.`is_forcedEviction` `fe`,
						 `im`.`is_admin` `adm`,
						 `img`.`grade_name`,
						 case when `im`.`sex` = :sex then "남" else "여" end sex_name 
					FROM `imi_members` `im`
						INNER JOIN `imi_member_grades` `img`
							ON `im`.`grade_code` = `img`.`grade_code`
					WHERE 1=1
				';

		$query .= $search;
		$query .= ' ORDER BY `im`.`name`, `im`.`id` ASC';

		// 회원정보조회 SQL.
		$stmt = $pdo->prepare($query);

		$stmt->bindValue(':sex','M');
	
		if($_SESSION['is_superadmin']=='N'){
			$stmt->bindValue(':is_superadmin','N');
		}
		if(!empty($search_name)){
			$stmt->bindValue(':name',$search_name);
		}
		$stmt->execute();  // 실행
		$members = $stmt->fetchAll(); // 회원데이터 
		
		// 설정값에 따라 향후 액션을 지정함.
		$choiceArray = ['Y'=>['value'=>'N','text'=>'설정해제'],
						'N'=>['value'=>'Y','text'=>'설정하기']];

		$setting_manager_url = $setting_forcedEviction_url = MEMBER_PROCESS_ACTION.'/member_process.php';
		
		// 관리자설정 action URL	
		$setting_manager_url .= '?mode=setting_manager';
		// 강제탈퇴 action URL
		$setting_forcedEviction_url .= '?mode=setting_forcedEviction';

		ob_Start();
		include __DIR__.'/../templates/admin.html.php'; // 템플릿
		$output = ob_get_clean();
	}catch(Exception $e) {
		$output = DB_CONNECTION_ERROR_MESSAGE.$e->getMessage().', 위치: '.$e->getFile().':'.$e->getLine();
	}

	include __DIR__ .'/../templates/layout.html.php'; // 전체 레이아웃
