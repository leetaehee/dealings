<?php
	/**
	 * 회원 현황
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
		$title = TITLE_ADMIN_MEMBER_STATUS . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN . '/admin_page.php';

		$alertMessage = '';

		$mIdx = $_SESSION['mIdx'];

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// 관리자 메뉴에서 회원 현황 조회.
		$rMemberP = [
		    'condition'=> 'M',
            'then'=> '남성',
            'else'=> '여성'
        ];

        $rMemberQ = 'SELECT `idx`,
							`id`,
							`name`,
							`email`,
							`phone`,
							`sex`,
							`join_approval_date`,
							`mileage`,
							CASE WHEN `sex` = ? then ? else ? end sex
					  FROM `th_members`
					  WHERE `forcedEviction_date` IS NULL
					  AND `withdraw_date` IS NULL
					  AND `join_approval_date` IS NOT NULL';

        $rMemberResult = $db->execute($rMemberQ, $rMemberP);
        if ($rMemberResult === false) {
            throw new Exception('회원현황을 조회하면서 오류가 발생했습니다.');
        }

        // 회원현황 데이터 추가
        $memberData = [];

        foreach ($rMemberResult as $key => $value) {

            $memberIdx = $rMemberResult->fields['idx'];
            $id = $rMemberResult->fields['id'];
            $name = $rMemberResult->fields['name'];
            $email = $rMemberResult->fields['email'];
            $phone = $rMemberResult->fields['phone'];
            $sex = $rMemberResult->fields['sex'];
            $joinApprovalDate = $rMemberResult->fields['join_approval_date'];
            $mileage = $rMemberResult->fields['mileage'];

            $memberData[] = [
                'seq'=> ($key+1),
                'member_idx'=> $memberIdx,
                'id'=> $id,
                'name'=> setDecrypt($name),
                'email'=> setDecrypt($email),
                'phone'=> setDecrypt($phone),
                'sex'=> $sex,
                'join_approval_date'=> $joinApprovalDate,
                'mileage'=> number_format($mileage)
            ];
        }

        $memberDataCount = count($memberData);
		
		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/member_status.html.php';
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