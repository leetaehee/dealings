<?php
	/**
	 * 판매 거래 목록
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
		$title = TITLE_VOUCHER_PURCHASE_LIST . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN.'/voucher_dealings.php';

		$alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// 거래 정보 조회
        $rDealingsP = [
            'dealingsType'=> '판매',
            'dealingsState'=> 1,
            'is_del'=> 'N'
        ];

        $rDealingsQ = 'SELECT `idx`,
                              `register_date`,
                              `dealings_subject`,
                              `dealings_status`,
                              `item_no`,
                              `item_money`,
                              `dealings_mileage`
                       FROM `th_dealings`
                       WHERE `dealings_type` = ?
                       AND `dealings_status` = ?
                       AND `is_del` = ?
                       ORDER BY `idx` DESC';

        $rDealingsResult = $db->execute($rDealingsQ, $rDealingsP);
        if ($rDealingsResult === false) {
            throw new Exception('거래 정보를 조회하면서 오류가 발생했습니다.');
        }

        $sellData = [];

        foreach ($rDealingsResult as $key => $value) {
            // 판매상세글 화면으로 이동
            $dealingsDetailViewHref = SITE_DOMAIN . '/dealings_sell_detail_view.php';

            $dealingsIdx = $value['idx'];
            $registerDate = $value['register_date'];
            $dealingsSubject = $value['dealings_subject'];
            $itemNo = $value['item_no'];
            $itemMoney = $value['item_money'];
            $dealingsMileage = $value['dealings_mileage'];
            $dealingsStatus = $value['dealings_status'];

            // 판매물품 조회
            $rSellItemQ = 'SELECT `item_name`
                           FROM `th_sell_item`
                           WHERE `idx` = ?';

            $rSellItemResult = $db->execute($rSellItemQ, $itemNo);
            if ($rSellItemResult === false) {
                throw new Exception('거래 물품명을 조회하면서 오류가 발생했습니다.');
            }

            $itemName = $rSellItemResult->fields['item_name'];

            // 구매 상세 화면으로 이동하기 위한 파라미터
            $dealingsDetailViewHref .= '?idx=' .$dealingsIdx. '&type=' . $dealingsStatus;

            $sellData[] = [
                'seq'=> $key+1,
                'dealings_idx'=> $dealingsIdx,
                'register_date'=> $registerDate,
                'dealings_subject'=> $dealingsSubject,
                'item_no'=> $itemNo,
                'item_name'=> $itemName,
                'item_money'=> $itemMoney,
                'dealings_mileage'=> $dealingsMileage,
                'detail_view_url'=> $dealingsDetailViewHref
            ];
        }

        $sellDataCount = count($sellData);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/voucher_sell_list.html.php';
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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_voucher.html.php'; // 전체 레이아웃