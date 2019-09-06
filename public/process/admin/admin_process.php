<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 
		1. 관리자 회원가입 처리 기능- 회원추가/회원수정/회원삭제 
		2. 모드- 회원가입: 'add', 회원수정: 'modi', 회원탈퇴: 'del'
		3. 회원 가입 성공 후, 세션에 다지 않고 로그인으로 가서 로그인하도록 할 것.
		4. 회원 탈퇴는 관리자는 할 수 없으며, 슈퍼관리자에게 권한해제를 요청해야 함.
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/mailer.lib.php'; // PHP메일보내기

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php'; // adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php'; // adodb

    // Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/AdminClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/LoginClass.php';

	$returnUrl = ''; // 리턴되는 화면 URL 초기화.
	$isValid = true;
	$mode = isset($_POST['mode']) ?  $_POST['mode'] : $_GET['mode'];

	if ($mode == 'add') {
		$adminClass = new AdminClass($db);

		// 폼 데이터 받아서 유효성 검증
		$returnUrl = SITE_ADMIN_DOMAIN.'/join.php'; // 유효성검증 실패시, 리턴 UTL

		$postData = $_POST;
		$resultMemberValidCheck = $adminClass->checkAdminFormValidate($postData);

		if ($resultMemberValidCheck['isValid'] == false) {
			alertMsg($returnUrl, 1, $resultMemberValidCheck['errorMessage']);
		} else {
			if ($adminClass->getIdOverlapCount($postData['id']) > 0) {
				alertMsg($returnUrl, 1, '아이디가 중복됩니다.');
			}
			if ($adminClass->getPhoneOverlapCount($postData['phone']) > 0) {
				alertMsg($returnUrl, 1, '핸드폰번호가 중복됩니다.');
			}
			if ($adminClass->getEmailOverlapCount($postData['email'])) {
				alertMsg($returnUrl, 1, '이메일이 중복됩니다.');
			}
		}

		if ($resultMemberValidCheck['isValid'] == true) {
			$insertResult = $adminClass->insertMember($postData);

			if ($insertResult > 0) {
				$returnUrl = SITE_ADMIN_DOMAIN.'/join_complete.php'; // 회원가입 화면 URL 지정(가입완료화면)

				$_SESSION['tmp_idx'] = 't_'.$idx; // 임시세션

				$approvalUrl = SITE_ADMIN_DOMAIN.'/join_approval.php?idx='.$insertResult; // 승인링크 추가
				$content = '<a href='.$approvalUrl.'>메일승인하러가기</a>';

				//메일발송
				mailer(MAIL_SENDER, MAIL_ADDRESS, $postData['email'], MAIL_TITLE, $content);

				alertMsg($returnUrl, 1, '관리자 회원가입이 완료되었습니다! 이메일을 확인하세요!');
			} else {
				alertMsg($returnUrl, 1, '관리자 회원가입이 실패하였습니다!');
			}
		} 
	} else if ($mode == 'modi') {
		// 회원정보수정
		$adminClass = new AdminClass($db);
		$postData = $_POST; // 폼데이터

		$returnUrl = SITE_ADMIN_DOMAIN.'/admin_modify.php?idx='.$postData['idx']; // 유효성검증 실패시, 리턴 UTL

		$resultAdminValidCheck = $adminClass->checkAdminFormValidate($postData);

		if ($resultAdminValidCheck['isValid'] == false) {
			alertMsg($returnUrl, 1, $resultAdminValidCheck['errorMessage']);
		} else {
			if ($adminClass->getPhoneOverlapCount($postData['phone']) > 0 && $postData['isOverlapPhone']==1) {
				alertMsg($returnUrl, 1, '핸드폰번호가 중복됩니다.');
			}
			if ($adminClass->getEmailOverlapCount($postData['email']) > 0  && $postData['isOverlapEmail']==1) {
				alertMsg($returnUrl, 1, '이메일이 중복됩니다.');
			}
		}

		if ($resultAdminValidCheck['isValid'] == true) {
			// 트랜잭션시작
			$db->beginTrans();

			$updateResult = $adminClass->updateAdmin($postData);

			if ($updateResult > 0) {
				$returnUrl = SITE_ADMIN_DOMAIN.'/admin_page.php'; // 수정 성공 시 마이페이지로 이동
			}else{
				$db->rollbackTrans();
				alertMsg($returnUrl, 1, '회원정보 수정이 실패하였습니다! 관리자에게 문의하세요');
			}
			
			$db->commitTrans();

			alertMsg($returnUrl, 1, '회원정보가 수정 되었습니다!');
		}
	} else if ($mode == 'del') {
		// 회원탈퇴
		$idx = isset($_GET['idx']) ? $_GET['idx'] : $_POST['idx'];
        $password = isset($_GET['password']) ? $_GET['password'] : $_POST['password'];
        
        $returnUrl = SITE_ADMIN_DOMAIN.'/admin_delete.php?idx='.$idx; // 유효성검증 실패시, 리턴 
        
        $adminClass = new AdminClass($db);
        $loginClass = new LoginClass($db);
        
        $param = [$idx, $password];
        
        // 트랜잭션시작
        $db->beginTrans();
        
        if ($loginClass->checkPasswordByAdmin($param)==false) {
            $db->rollbackTrans();
			alertMsg($returnUrl, 1, '비밀번호를 확인하세요');            
        }else{
            $returnUrl = SITE_ADMIN_DOMAIN;
        }
        
        $updateResult = $adminClass->deleteAdmin($idx);
        
        if ($updateResult < 1) {
			$db->rollbackTrans();
            alertMsg($returnUrl,1,'오류! 관리자에게 문의하세요!');
        }
        
        $db->commitTrans();
        
        session_destroy();
        alertMsg($returnUrl,1,'정상적으로 탈퇴되었습니다. 이용해주셔서 감사합니다.');
	}