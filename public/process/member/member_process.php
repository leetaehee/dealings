<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 
		1. 회원가입 처리 기능- 회원추가/회원수정/회원삭제 
		2. 모드- 회원가입: 'add', 회원수정: 'modi', 회원탈퇴: 'del'
		3. 회원 가입 성공 후, 세션에 다지 않고 로그인으로 가서 로그인하도록 할 것.
		4. 회원 탈퇴는 관리자는 할 수 없으며, 슈퍼관리자에게 권한해제를 요청해야 함.
	 */

	// 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	// 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	// 공통함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	// PHP메일보내기
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/mailer.lib.php';
	// PDO 객체 생성
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/databaseConnection.php';

	try {
		$returnUrl = ''; // 리턴되는 화면 URL 초기화.
		$isValid = true;
		$result = false;
		$insertResult = 0;
		$mode = isset($_POST['mode']) ?  $_POST['mode'] : $_GET['mode'];

		if ($mode == 'add') {
			// 유효성검증 실패시, 리턴 UTL
			$returnUrl = SITE_DOMAIN.'/join.php';

			$postData = [
					'mode'=>$mode,
					'id'=>isset($_POST['id']) ? $_POST['id'] : '',
					'password'=>isset($_POST['password']) ? $_POST['password'] : '',
					'repassword'=>isset($_POST['repassword']) ? $_POST['repassword'] : '',
					'name'=>isset($_POST['name']) ? $_POST['name'] : '',
					'email'=>isset($_POST['email']) ? $_POST['email'] : '',
					'phone'=>isset($_POST['phone']) ? $_POST['phone'] : '',
					'birth'=>isset($_POST['birth']) ? $_POST['birth'] : '',
					'sex'=>isset($_POST['sex']) ? $_POST['sex'] : '',
					'account_no'=>isset($_POST['account_no']) ? $_POST['account_no'] : '',
					'account_bank'=>isset($_POST['account_bank']) ? $_POST['account_bank'] : ''
				];

			// 핸드폰번호에 하이픈을 제거한다
			$phone = $postData['phone'];

			if (!empty(strstr($phone, '-'))) {
				$phone = str_replace('-', '' , $phone);
			}
			
			if (checkMemberFormValidate($postData) == false) {
				$isValid = false; // 유효성검증
			} else {
				if (getIsCheckAccountOverlap($pdo, $postData) == false) {
					$isValid = false; // 계정 중복체크 (아이디,이메일,핸드폰)
				}
			}

			if ($isValid == false) { 
				alertMsg($returnUrl);
			}	

			if ($isValid == true) {
				// 회원가입 화면 URL 지정(가입완료화면)
				$returnUrl = SITE_DOMAIN.'/join_complete.php';
				// 비밀번호 암호화(단방향)
				$encryptedPassword = password_hash($postData['password'], PASSWORD_DEFAULT); 
				// 등급 가져오기(2019.08.29 사용안함) 
				//$gradeCode = getLowGrade($pdo);

				// Insert SQL
				$query = 'INSERT INTO `imi_members` SET
								`id` = :id,
								`grade_code` = :grade_code,
								`admin_grade` = :admin_grade,
								`password` = :password,
								`email` = :email,
								`name` = :name,
								`phone` = :phone,
								`sex` = :sex,
								`birth` = :birth,
								`account_no` = :account_no,
								`account_bank` = :account_bank,
								`join_date` = CURDATE()';
				$stmt = $pdo->prepare($query);
				$stmt->bindValue(':id', $postData['id']);
				$stmt->bindValue(':grade_code', 3);
				$stmt->bindValue(':admin_grade', 0);
				$stmt->bindValue(':password', $encryptedPassword);
				$stmt->bindValue(':email', setEncrypt($postData['email']));
				$stmt->bindValue(':name', setEncrypt($postData['name']));
				$stmt->bindValue(':phone', setEncrypt($phone));
				$stmt->bindValue(':sex', $postData['sex']);
				$stmt->bindValue(':birth', setEncrypt($postData['birth']));
				$stmt->bindValue(':account_no', setEncrypt($postData['account_no']));
				$stmt->bindValue(':account_bank', $postData['account_bank']);
				
				// 트랜잭션 추가.
				$pdo->beginTransaction();
				$result = $stmt->execute(); // 회원정보 추가
				$idx = $pdo->lastInsertId(); // primary key 구하기
				$pdo->commit();

				if ($result == true) {
					// 회원 가입 화면을 보여주기 위한 Session 변수 만들기
					$_SESSION['tmp_idx'] = 't_'.$idx;

					// 승인링크 추가
					$approvalUrl = SITE_DOMAIN.'/join_approval.php?idx='.$idx;
					$content = '<a href='.$approvalUrl.'>메일승인하러가기</a>';

					//메일발송
					mailer(MAIL_SENDER, MAIL_ADDRESS, $postData['email'], MAIL_TITLE, $content);
				}
				alertMsg($returnUrl);
			}
		}
	} catch (Exception $e){
		$pdo->rollback();
		$output = DB_CONNECTION_ERROR_MESSAGE . $e->getMessage() . ', 위치: ' . $e->getFile() . ':' . $e->getLine();
		echo $output;
	}