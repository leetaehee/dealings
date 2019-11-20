<?php
	/**
	 * 나의 구매글 등록 현황
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
		$title = TITLE_VOUCHER_PURCHASE_ENROLL_STATUS . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN . '/voucher_dealings.php';

        $alertMessage = '';
		$dealingsType = '구매';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

        // 내가 구매한 목록 조회
        $rPurchaseListP = [
            'dealings_type'=> $dealingsType,
            'is_del'=> 'N',
            'writer_idx'=> $_SESSION['idx']
        ];

        $rPurchaseListQ = 'SELECT `idx`,
                                  `dealings_status`,
                                  `dealings_subject`,
                                  `item_money`,
                                  `item_object_no`,
                                  `dealings_mileage`,
                                  `item_no`
                           FROM `th_dealings`
                           WHERE `dealings_type` = ?
                           AND `is_del` = ?
                           AND `writer_idx` = ?';

        $rPurchaseListResult = $db->execute($rPurchaseListQ, $rPurchaseListP);
        if ($rPurchaseListResult === false) {
            throw new Exception('거래정보를 조회하면서 오류가 발생했습니다.');
        }

        $myPurchaseData = [];

        // 거래 상세화면 이동
        $DealingsDetailViewHref = SITE_DOMAIN . '/my_purchase_dealings_status.php';

        foreach ($rPurchaseListResult as $key => $value) {

            $dealingsIdx = $rPurchaseListResult->fields['idx'];
            $itemNo = $rPurchaseListResult->fields['item_no'];
            $dealingsStatus = $rPurchaseListResult->fields['dealings_status'];
            $itemMoney = $rPurchaseListResult->fields['item_money'];
            $dealingsMileage = $rPurchaseListResult->fields['dealings_mileage'];
            $dealingsSubject = $rPurchaseListResult->fields['dealings_subject'];

            // 거래 날짜 조회
            $rDealingsUserQ = 'SELECT `dealings_date` 
                               FROM `th_dealings_user`
                               WHERE `dealings_idx` = ?';

            $rDealingsUserResult = $db->execute($rDealingsUserQ, $dealingsIdx);
            if ($rDealingsUserResult === false) {
                throw new Exception('거래날짜를 조회하면서 오류가 발생했습니다.');
            }

            $dealingsDate = $rDealingsUserResult->fields['dealings_date'];

            // 거래물품이름 조회
            $rItemNoQ = 'SELECT `item_name` 
                         FROM `th_sell_item`
                         WHERE `idx` = ?';

            $rItemNoResult = $db->execute($rItemNoQ, $itemNo);
            if ($rItemNoResult === false) {
                throw new Exception('거래물품이름을 조회하면서 오류가 발생했습니다.');
            }

            $itemName = $rItemNoResult->fields['item_name'];

            // 거래 상태 조회
            $rDealingsStatusQ = 'SELECT `dealings_status_name`
                                 FROM `th_dealings_status_code`
                                 WHERE idx = ?';

            $rDealingsStatusResult = $db->execute($rDealingsStatusQ, $dealingsStatus);
            if ($rDealingsStatusResult === false) {
                throw new Exception('거래상태명을 조회하면서 오류가 발생했습니다.');
            }

            $dealingsStatusName = $rDealingsStatusResult->fields['dealings_status_name'];

            // 구매 상세 화면으로 이동하기 위한 파라미터
            $DealingsDetailViewHref .= '?type=' . $dealingsStatus . '&idx=' . $dealingsIdx;

            // 사용자에게 보여질 데이터.
            $myPurchaseData[] = [
                'seq'=> $key+1,
                'item_name' => $itemName,
                'dealings_idx'=> $dealingsIdx,
                'dealings_subject'=> $dealingsSubject,
                'dealings_status_name'=> $dealingsStatusName,
                'dealings_date'=> $dealingsDate,
                'item_money'=> $itemMoney,
                'dealings_mileage'=> $dealingsMileage,
                'url'=> $DealingsDetailViewHref
            ];
        }

        $myPurchaseDataCount = count($myPurchaseData);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/my_purchase_list.html.php';
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