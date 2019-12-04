<?php
	/**
	 * 관리자 수정 화면
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php';

	// adodb
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	try {
		$title = TITLE_ADMIN_MODIFY_MENU . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN.'/admin_page.php';
		$alertMessage = '';

		$actionUrl = ADMIN_PROCESS_ACTION . '/modify_member.php';
		$ajaxUrl = ADMIN_PROCESS_ACTION . '/admin_ajax_process.php';
		$JsTemplateUrl = JS_ADMIN_URL . '/join.js'; 

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
                            CASE WHEN `sex` = ? then "?" else "?" end sex_name,
                            `sex`,
                            `birth`
                    FROM `th_admin` 
                    WHERE `idx` = ?';

        $rAdminResult = $db->execute($rAdminQ, $rAdminP);
        if ($rAdminResult === false) {
            throw new Exception('관리자 정보를 조회하면서 오류가 발생했습니다.');
        }

        $adminId = $rAdminResult->fields['id'];
        $adminName = setDecrypt($rAdminResult->fields['name']);
        $adminEmail = setDecrypt($rAdminResult->fields['email']);
        $adminPhone = setDecrypt($rAdminResult->fields['phone']);
        $adminBirth = setDecrypt($rAdminResult->fields['birth']);

        $adminSex = $rAdminResult->fields['sex'];
        $adminSexMChecked = 'checked';
        $adminSexWChecked = '';

        if ($adminSex === 'M') {
            $adminSexMChecked = 'checked';
        } else {
            $adminSexWChecked = 'checked';
        }

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/join.html.php';
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