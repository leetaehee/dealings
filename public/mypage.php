<?php
	/**
	 * 마이페이지
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
		$title = TITLE_MYPAGE_MENU . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN;

		$alertMessage = '';
		
		$memberIdx = $_SESSION['idx'];

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// 회원 상세 정보 조회
        $rMemberP = [
            'condition'=> 'M',
            'then'=> '남성',
            'else'=> '여성',
            'member_idx'=> $memberIdx
        ];

        $rMemberQ = 'SELECT `id`,
                            `name`,
                            `email`,
                            `phone`,
							`mileage`,
                             CASE WHEN `sex` = ? then ? else ? end sex_name,
                             `sex`,
                             `birth`,
                             `join_date`,
                             `join_approval_date`,
							 `mileage`,
							 `account_no`,
							 `account_bank`,
							 `grade_code`
                      FROM `th_members`
                      WHERE `idx` = ?';

        $rMemberResult = $db->execute($rMemberQ, $rMemberP);
        if ($rMemberResult === false) {
            throw new Exception('회원 상세정보를 조회하면서 오류가 발생했습니다.');
        }

        $gradeCode = $rMemberResult->fields['grade_code'];

        // 회원 등급명 조회
        $rMemberGradeQ = 'SELECT `grade_name` 
                          FROM `th_member_grades`
                          WHERE `grade_code` = ?';

        $rMemberGradeResult = $db->execute($rMemberGradeQ, $gradeCode);
        if ($rMemberGradeResult === false) {
            throw new Exception('회원 등급명을 조회하면서 오류가 발생했습니다.');
        }

        $gradeName = $rMemberGradeResult->fields['grade_name'];

        $id = $rMemberResult->fields['id'];
        $name = setDecrypt($rMemberResult->fields['name']);
        $email = setDecrypt($rMemberResult->fields['email']);
        $phone = setDecrypt($rMemberResult->fields['phone']);
        $mileage = $rMemberResult->fields['mileage'];
        $sex = $rMemberResult->fields['sex_name'];
        $birth = setDecrypt($rMemberResult->fields['birth']);
        $joinDate = $rMemberResult->fields['join_date'];
        $joinApprovalDate = $rMemberResult->fields['join_approval_date'];
        $mileage = $rMemberResult->fields['mileage'];
        $accountNo = setDecrypt($rMemberResult->fields['account_no']);
        $accountBank = $rMemberResult->fields['account_bank'];

        // 회원탈퇴
        $memberDeleteUrl = SITE_DOMAIN . '/member_delete.php';
        // 회원수정
        $memberModifyUrl = SITE_DOMAIN . '/member_modify.php';
        // 계좌설정
        $myAccountSetUrl = SITE_DOMAIN . '/my_account.php';

        $templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/mypage.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if ($connection === true) {
			$db->close();
		}

		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃