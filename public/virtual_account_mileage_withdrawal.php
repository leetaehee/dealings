<?php
	/**
	 * 가상계좌 출금 등록 화면
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
		$title = TITLE_VIRTUAL_MILEGE_WITHDRAWAL . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화
		$alertMessage = '';

		$actionUrl = MILEAGE_PROCESS_ACTION . '/virtual_account_withdrawal.php';
		$JsTemplateUrl = JS_URL . '/virtual_account_withdrawal.js';

        if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$memberIdx = $_SESSION['idx'];
		$mileageType = 5;

		// 출금계좌 조회
        $rMyAccountQ = 'SELECT `account_no`,
							   `account_bank`
					    FROM `th_members`
					    WHERE `idx` = ?';

        $rMyAccountResult = $db->execute($rMyAccountQ, $memberIdx);
        if ($rMyAccountResult === false) {
            throw new RollbackException('계좌정보를 조회하면서 오류가 발생했습니다.');
        }

        $accountNo = setDecrypt($rMyAccountResult->fields['account_no']);
        $accountBank = $rMyAccountResult->fields['account_bank'];

        if (empty($accountNo)) {
            $returnUrl = $returnUrl . '/mypage.php';
            throw new Exception('마이룸 > 내정보조회 > 출금계좌설정에서 설정하세요!');
        }

        // 출금마일리지 타입 조회
        $mileageTypeName = $CONFIG_MILEAGE_TYPE_COLUMN[$mileageType];

        // 사용가능한 마일리지 조회
        $rMileageTypeQ = "SELECT `{$mileageTypeName}`
					      FROM `th_mileage_type_sum`
					      WHERE `member_idx` = ?";

        $rMileageTypeResult = $db->execute($rMileageTypeQ, $memberIdx);
        if ($rMileageTypeResult === false) {
            throw new Exception('마일리지를 조회하면서 오류가 발생했습니다.');
        }

        // 보유마일리지가 있는지 확인
        $maxMileage = $rMileageTypeResult->fields[$mileageTypeName];
        if ($maxMileage < 0) {
            throw new Exception('마일리지 조회 오류! 관리자에게 문의하세요.');
        }

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/virtual_account_mileage_withdrawal.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if ($connection == true) {
			$db->close();
		}

		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃