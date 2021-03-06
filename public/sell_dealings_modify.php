<?php
	/**
	 * 판매 거래 수정 화면
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
		$title = TITLE_VOUCHER_SELL_MODIFY . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN . '/voucher_dealings.php';

		$alertMessage = '';

		$actionUrl = DEALINGS_PROCESS_ACCTION . '/dealings_modify.php';
		$JsTemplateUrl = JS_URL . '/voucher_sell_enroll.js';

		$dealingsIdx = $_GET['idx'];

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

        // 거래정보 조회
        $rDealingsQ = 'SELECT `dealings_subject`,
							  `dealings_content`,
							  `memo`,
							  `item_no`,
							  `item_money`,
							  `item_object_no`
                       FROM `th_dealings`
                       WHERE `idx` = ?';

        $rDealingsResult = $db->execute($rDealingsQ, $dealingsIdx);
        if ($rDealingsResult === false) {
            throw new Exception('거래 정보를 조회하면서 오류가 발생했습니다.');
        }

        $dealingsSubject = $rDealingsResult->fields['dealings_subject'];
        $dealingsContent = $rDealingsResult->fields['dealings_content'];
        $dealingsMemo = $rDealingsResult->fields['memo'];
        $itemMoney = $rDealingsResult->fields['item_money'];
        $itemObjectNo = $rDealingsResult->fields['item_object_no'];

        // 상품권 리스트
        $isSell = 'Y';

        $rSellItemQ = 'SELECT `item_name`
                       FROM `th_sell_item`
                       WHERE `is_sell` = ?';

        $rSellItemResult = $db->execute($rSellItemQ, $isSell);
        if ($rSellItemResult === false) {
            throw new Exception('판매물품명을 조회하면서 오류가 발생하였습니다.');
        }

        $itemName = $rSellItemResult->fields['item_name'];

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/sell_dealings_modify.html.php';
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