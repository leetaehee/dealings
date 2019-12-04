<?php
	/**
	 * 쿠폰 지급 대상 리스트 (회원 리스트)
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
		$title = TITLE_COUPON_ISSUE_MEMBER . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN . '/coupon.php';

		$alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// 쿠폰 지급 할 수 있는 대상 조회
		$rMemberP = [
		  'case'=> 'M',
          'then'=> '남성',
          'else'=> '여성',
        ];

		$rMemberQ = 'SELECT `idx`,
							`id`,
							`name`,
							`email`,
							`phone`,
							`sex`,
							`mileage`,
							CASE WHEN `sex` = ? then ? else ? end sex_name
		             FROM `th_members`
		             WHERE `forcedEviction_date` IS NULL
					 AND `withdraw_date` IS NULL
					 AND `join_approval_date` IS NOT NULL';

		$rMemberResult = $db->execute($rMemberQ, $rMemberP);
		if ($rMemberResult === false) {
		    throw new Exception('쿠폰 지급 대상자를 조회하면서 오류가 발생했습니다.');
        }

		// 쿠폰 지급 대상자 정보 추가
		$memberData = [];

		foreach ($rMemberResult as $key => $value) {
		    $memberIdx = $rMemberResult->fields['idx'];
		    $id = $rMemberResult->fields['id'];
		    $name = $rMemberResult->fields['name'];
		    $email = $rMemberResult->fields['email'];
		    $phone = $rMemberResult->fields['phone'];
		    $sex = $rMemberResult->fields['sex_name'];
		    $mileage = $rMemberResult->fields['mileage'];

            $couponProvideURL = $couponProvideStatusURL = SITE_ADMIN_DOMAIN;

            // 쿠폰 발급해주는 URL
            $couponProvideURL .= '/coupon_provider.php?idx=' . $memberIdx;
            // 쿠폰 발급 현황 URL
            $couponProvideStatusURL .= '/coupon_provider_status.php?idx=' . $memberIdx;

		    $memberData[] = [
		      'seq'=> ($key+1),
		      'idx'=> $memberIdx,
              'id'=> $id,
              'name'=> setDecrypt($name),
              'email'=> setDecrypt($email),
              'phone'=> setDecrypt($phone),
              'sex'=> $sex,
              'mileage'=> $mileage,
              'coupon_provide_url'=> $couponProvideURL,
              'coupon_provide_status_url'=> $couponProvideStatusURL
            ];
        }

        $memberDataCount = count($memberData);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/coupon_memeber_stauts.html.php';
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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_coupon.html.php';// 전체 레이아웃