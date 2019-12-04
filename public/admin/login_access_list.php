<?php
	/**
	 * 로그인내역
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
		$title = TITLE_ADMIN_LOGIN_LIST . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN;

		$alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$mIdx = $_SESSION['mIdx'];

		// 관리자 접속내역 조회
		$rLoginAccessP = [
			'idx'=> $_SESSION['mIdx'],
			'today'=> date('Y-m-d')
		];

        $rLoginAccessQ = 'SELECT `idx`,
                                 `admin_idx`,
                                 `access_ip`,
                                 `access_date`,
                                 `access_datetime`
                          FROM `th_admin_access_ip`
                          WHERE `admin_idx` = ?
                          AND `access_date` = ?';

        $rLoginAccessResult = $db->execute($rLoginAccessQ, $rLoginAccessP);
        if ($rLoginAccessResult === false) {
            throw new Exception('관리자 접속 내역을 조회하면서 오류가 발생했습니다.');
        }

        // 관리자 접속내역 추가
        $loginAccessData = [];

        foreach ($rLoginAccessResult as $key => $value) {

            $accessIdx = $rLoginAccessResult->fields['idx'];
            $adminIdx = $rLoginAccessResult->fields['admin_idx'];
            $accessIp = $rLoginAccessResult->fields['access_ip'];
            $accessDate = $rLoginAccessResult->fields['access_date'];
            $accessDatetime = $rLoginAccessResult->fields['access_datetime'];

            $loginAccessData[] = [
                'seq'=> ($key+1),
                'access_idx'=> $accessIdx,
                'admin_idx'=> $adminIdx,
                'access_ip'=> setDecrypt($accessIp),
                'access_date'=> $accessDate,
                'access_datetime'=> $accessDatetime

            ];
        }

        $loginAccessDataCount = count($loginAccessData);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/login_access_list.html.php';
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