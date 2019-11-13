<?php
	/**
	 * 회원수정 화면
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php';

	// adodb
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';
	
	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_MODIFY_MENU . ' | ' . TITLE_SITE_NAME;

		$returnUrl = SITE_DOMAIN.'/mypage.php'; // 리턴되는 화면 URL 초기화.
		$alertMessage = '';

		$actionUrl = MEMBER_PROCESS_ACTION . '/modify_member.php'; // form action url
		$ajaxUrl = MEMBER_PROCESS_ACTION . '/member_ajax_process.php'; // ajax url
		$JsTemplateUrl = JS_URL . '/join.js'; 

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

        $memberIdx = $_SESSION['idx'];

		// 회원정보 조회
        $rMemberQ = 'SELECT `id`,
                             `name`,
                             `email`,
                             `phone`,
                             `sex`,
                             `birth`
                       FROM `th_members`
                       WHERE `idx` = ?';

        $rMemberResult = $db->execute($rMemberQ, $memberIdx);
        if ($rMemberResult == false) {
            throw new Exception('회원 정보를 조회하면서 오류가 발생했습니다.');
        }

        $userId = $rMemberResult->fields['id'];
        $userName = setDecrypt($rMemberResult->fields['name']);
        $userEmail = setDecrypt($rMemberResult->fields['email']);
        $userPhone = setDecrypt($rMemberResult->fields['phone']);
        $userBirth = setDecrypt($rMemberResult->fields['birth']);
        $userSex = $rMemberResult->fields['sex'];

        $userSexMChecked = 'checked';
        $userSexWChecked = '';
        if ($userSex == 'M') {
            $userSexMChecked = 'checked';
        } else {
            $userSexWChecked = 'checked';
        }

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/join.html.php'; // 템플릿
	} catch (Exception $e) {
		if ($connection === true) {
			$alertMessage = $e->getMessage();
		}
	} finally {
		if ($connection === true) {
			$db->close();
		}

		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃