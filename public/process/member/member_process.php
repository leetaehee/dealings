<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 
		1. 회원가입 처리 기능- 회원추가/회원수정/회원삭제 
		2. 모드- 회원가입: 'add', 회원수정: 'modi', 회원탈퇴: 'del'
		3. 회원 가입 성공 후, 세션에 다지 않고 로그인으로 가서 로그인하도록 할 것.
		4. 회원 탈퇴는 관리자는 할 수 없으며, 슈퍼관리자에게 권한해제를 요청해야 함.
	 */

	 $top_dir = '/../../..';

	 include __DIR__.$top_dir.'/configs/config.php'; // 환경설정
	 include __DIR__.$top_dir.'/messages/message.php'; // 메세지
	 include __DIR__.$top_dir.'/includes/function.php'; // 공통함수
	 include __DIR__.$top_dir.'/includes/mailer.lib.php'; // PHP메일보내기

	 $return_url = ''; // 리턴되는 화면 URL 초기화.
	 $is_valid_falid = true; // 유효성 검증 실패시에 체크하는 변수
	 $mode = isset($_POST['mode']) ?  $_POST['mode'] : $_GET['mode'];

	 if($mode=='add'){
		// 회원가입
		try {
			include __DIR__.$top_dir.'/includes/databaseConnection.php'; // PDO 객체 생성

			// 유효성검증 실패시, 리턴 UTL
			$return_url = SITE_DOMAIN.'/join.php';
			
			// 유효성검증
			if(check_memberForm_validate($_POST,$pdo)==false){
				$is_valid_falid = false; 
			}

			if($is_valid_falid==false){
				header('location: '.$return_url);
			}

			// 핸드폰번호에 하이픈을 제거한다.
			$phone = $_POST['phone'];
			if(!empty(strstr($_POST['phone'],'-'))){
				$phone = str_replace('-','',$_POST['phone']);
			}

			// 회원가입 화면 URL 지정(가입완료화면)
			$return_url = SITE_DOMAIN.'/join_complete.php';
			
			// 비밀번호 암호화
			$encrypted_password = password_hash($_POST['password'],PASSWORD_DEFAULT);

			// 등급 가져오기 (하위 등급 가져오기)
			$grade_code = getLowGrade($pdo);

			// DB에 추가
			$stmt = $pdo->prepare('
						INSERT INTO `imi_members` SET
							`id` = :id,
							`grade_code` = :grade_code,
							`password` = :password,
							`email` = :email,
							`name` = :name,
							`phone` = :phone,
							`sex` = :sex,
							`birth` = :birth,
							`join_date` = CURDATE()
						');
			$stmt->bindValue(':id',$_POST['id']);
			$stmt->bindValue(':grade_code',$grade_code);
			$stmt->bindValue(':password',$encrypted_password);
			$stmt->bindValue(':email',$_POST['email']);
			$stmt->bindValue(':name',$_POST['name']);
			$stmt->bindValue(':phone',$phone);
			$stmt->bindValue(':sex',$_POST['sex']);
			$stmt->bindValue(':birth',$_POST['birth']);
			$result = $stmt->execute(); // 회원정보 추가

			$idx = $pdo->lastInsertId(); // primary key 구하기 

			// 초기화
			session_destroy();
			session_start();
			// 회원 가입 화면을 보여주기 위한 Session 변수 만들기
			$_SESSION['tmp_idx'] = 't_'.$idx;
			// 승인링크 추가
			$approval_url = SITE_DOMAIN.'/join_approval.php?idx='.$idx;
			$content = '<a href='.$approval_url.'>메일승인하러가기</a>';

			mailer(MAIL_SENDER,MAIL_ADDRESS,$_POST['email'],MAIL_TITLE,$content); //메일발송
			
			header('location: '.$return_url);
		}catch(Exception $e){
			$output = DB_CONNECTION_ERROR_MESSAGE.$e->getMessage().', 위치: '.$e->getFile().':'.$e->getLine();
			echo $output;
		}
	 }elseif($mode=='modi'){
		// 회원정보수정
	 }elseif($mode=='del'){
		 // 회원탈퇴 
		 try {
			include __DIR__.$top_dir.'/includes/databaseConnection.php'; // PDO 객체 생성

			$stmt = $pdo->prepare('
						update `imi_members`
						 set `withdraw_date` = CURDATE(),
							 `modify_date` = CURDATE()
						 where `idx` = :idx
					');
			 $stmt->bindValue(':idx',$_GET['idx']);
			 $result = $stmt->execute();

			 // 세션제거
			 session_destroy();
			 // 처음화면이동
			 header('location: '.SITE_DOMAIN);
		 } catch(Exception $e) {
			$output = DB_CONNECTION_ERROR_MESSAGE.$e->getMessage().', 위치: '.$e->getFile().':'.$e->getLine();
			echo $output;
		 }
	 }elseif($mode=='setting_manager' || $mode=='setting_forcedEviction'){
		 // 강제탈퇴, 관리자 설정 
		 $position_column = ['setting_manager'=>'is_admin','setting_forcedEviction'=>'is_forcedEviction'];
		 $column = $position_column[$mode];
		
		 try {
			include __DIR__.$top_dir.'/includes/databaseConnection.php'; // PDO 객체 생성

			$return_url = SITE_DOMAIN.'/admin.php';
			
			if($mode=='setting_manager'){
				// 관리자설정
				$stmt = $pdo->prepare('
							UPDATE `imi_members`
							SET `is_admin` = :is_admin
							WHERE `idx` = :idx
						');
				$stmt->bindValue(':is_admin',$_GET['value']);
				$stmt->bindValue(':idx',$_GET['idx']);
				$result = $stmt->execute();
			}else if($mode=='setting_forcedEviction'){
				// 강제탈퇴 처리
				if($_GET['value']=='Y'){
					$stmt = $pdo->prepare('
								UPDATE `imi_members`
								SET `is_forcedEviction` = :is_admin,
									`forcedEviction_date` = CURDATE()
								WHERE `idx` = :idx
							');
				}else{
					$stmt = $pdo->prepare('
								UPDATE `imi_members`
								SET `is_forcedEviction` = :is_admin,
									`forcedEviction_date` = NULL
								WHERE `idx` = :idx
							');				
				}
				$stmt->bindValue(':is_admin',$_GET['value']);
				$stmt->bindValue(':idx',$_GET['idx']);
				$result = $stmt->execute();
			}
			header('location: '.$return_url);
		 } catch(Exception $e) {
			$output = DB_CONNECTION_ERROR_MESSAGE.$e->getMessage().', 위치: '.$e->getFile().':'.$e->getLine();
			echo $output;
		 }	 
	 }

