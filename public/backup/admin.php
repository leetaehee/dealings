<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 관리자페이지 
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
		$title = TITLE_ADMIN_MENU. ' | ' . TITLE_SITE_NAME;

		// form 전송시 전달되는 URL.
		$actionUrl = SITE_DOMAIN. '/admin.php';
		
		//이름이 암호화 되어있어서 검색기능은 사용하지 않음
		$search_name = '';
		/*
		if (isset($_POST['search_name'])) {
			$search_name = $_POST['search_name'];
		}
		*/
		
		$result = 0; // 쿼리 실행결과
		$search = '';
        
		if ($_SESSION['admin_grade'] == 1) {
			$search = ' AND `img2`.`grade_code` = :grade_code';
		}

		if (!empty($search_name)) {
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
						 `img`.`grade_name` `nm_grade_name`,
						 `img2`.`grade_name`,
						 case when `im`.`admin_grade` = "1" then "Y" else "N" end adm,
						 case when `im`.`sex` = :sex then "남" else "여" end sex_name
					FROM `imi_members` `im`
						LEFT JOIN `imi_member_grades` `img`
							ON `im`.`grade_code` = `img`.`grade_code`
						LEFT JOIN `imi_member_grades` `img2`
							ON `im`.`admin_grade` = `img2`.`grade_code`
					WHERE 1=1
				';

		$query .= $search;
		$query .= ' ORDER BY `im`.`name`, `im`.`id` ASC';

		// 회원정보조회 SQL.
		$stmt = $pdo->prepare($query);
		
		$stmt->bindValue(':sex', 'M');

		if ($_SESSION['admin_grade'] == 1) {
			$stmt->bindValue(':grade_code', 1);
		}

		if (!empty($search_name)) {
			$stmt->bindValue(':name', $search_name);
		}
		$result = $stmt->execute();  // 실행
		
		if ($result > 0) {
			$members = $stmt->fetchAll(); // 회원데이터 
		}
		
		// 설정값에 따라 액션을 지정함.
		$choiceArray = [
				'Y'=>[
					'value'=>'N',
					'integer'=>0,
					'text'=>'설정해제'
				],
				'N'=>[
					'value'=>'Y',
					'integer'=>1,
					'text'=>'설정하기'
				]
			];

		$settingManagerUrl = MEMBER_PROCESS_ACTION.'/admin_process.php';
		$settingForcedEvictionUrl = MEMBER_PROCESS_ACTION.'/admin_process.php';
		
		// 관리자설정 action URL	
		$settingManagerUrl .= '?mode=setting_manager';
		// 강제탈퇴 action URL
		$settingForcedEvictionUrl .= '?mode=setting_forcedEviction';

		ob_Start();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin.html.php'; // 템플릿
		$output = ob_get_clean();
	} catch(Exception $e) {
		$output = DB_CONNECTION_ERROR_MESSAGE.$e->getMessage().', 위치: '.$e->getFile().':'.$e->getLine();
	}

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout.html.php'; // 전체 레이아웃
