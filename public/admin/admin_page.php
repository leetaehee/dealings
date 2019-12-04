<?php
	/**
	 * 관리자 정보 조회
	 */

	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php';
    
	// adodb
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	// Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/AdminClass.php';

	try {
		$title = TITLE_ADMIN_MENU . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN;

		$alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$adminIdx = $_SESSION['mIdx'];

        // 관리자 정보 조회
        $rAdminP = [
            'sex_condition'=> 'M',
            'sex_then'=> '남성',
            'sex_else'=> '여성',
            'admin_idx'=> $adminIdx
        ];

        $rAdminQ = 'SELECT `idx`,
                            `id`,
                            `name`,
                            `email`,
                            `phone`,
                            CASE WHEN `sex` = ? then ? else ? end sex_name,
                            `sex`,
                            `birth`,
                            `join_date`,
                            `join_approval_date`,
							`is_superadmin`
                    FROM `th_admin` 
                    WHERE `idx` = ?';

        $rAdminResult = $db->execute($rAdminQ, $rAdminP);
        if ($rAdminResult === false) {
            throw new Exception('관리자 정보를 조회하면서 오류가 발생했습니다.');
        }

        $id = $rAdminResult->fields['id'];
        $name = setDecrypt($rAdminResult->fields['name']);
        $email = setDecrypt($rAdminResult->fields['email']);
        $phone = setDecrypt($rAdminResult->fields['phone']);
        $sex = $rAdminResult->fields['sex_name'];
        $birth = setDecrypt($rAdminResult->fields['birth']);
        $joinDate = $rAdminResult->fields['join_date'];
        $joinApprovalDate = $rAdminResult->fields['join_approval_date'];
        $isSuperAdmin = $rAdminResult->fields['is_superadmin'];

        $adminDeleteUrl = $adminModifyUrl = SITE_ADMIN_DOMAIN;

        // 회원탈퇴
		$adminDeleteUrl .= '/admin_delete.php?idx=' . $adminIdx;
        // 회원수정
		$adminModifyUrl .= '/admin_modify.php?idx=' . $adminIdx;

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/admin_page.html.php';
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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_main.html.php'; // 전체 레이아웃