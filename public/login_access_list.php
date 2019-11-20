<?php
	/**
	 * 회원 로그인 접속내역
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
		$title = TITLE_LOGIN_LIST . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN;

		$alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

        $rLoginAccessP = [
            'idx'=> $_SESSION['idx'],
            'today'=> date('Y-m-d')
        ];

        $rLoginAccessQ = 'SELECT `idx`,
                                 `member_idx`,
                                 `access_ip`,
                                 `access_date`,
                                 `access_datetime`
                          FROM `th_access_ip`
                          WHERE `member_idx` = ?
                          AND `access_date` = ?';

        $rLoginAccessResult = $db->execute($rLoginAccessQ, $rLoginAccessP);
        if ($rLoginAccessResult === false) {
            throw new Exception('회원 로그인 접속내역을 조회하면서 오류가 발생했습니다.');
        }

        $loginAccessCount = $rLoginAccessResult->recordCount();

        $templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/login_access_list.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if ($connection == true) {
			$db->close();
		}

		if (!empty($alertMessage)) {
			alertMsg(SITE_DOMAIN,1,$alertMessage);
		}
	}

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃