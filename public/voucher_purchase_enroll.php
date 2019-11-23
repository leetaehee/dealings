<?php
	/**
	 * 구매 거래 등록화면
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
		$title = TITLE_VOUCHER_PURCHASE_ENROLL . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN.'/voucher_dealings.php';
		$alertMessage = '';

		$actionUrl = DEALINGS_PROCESS_ACCTION . '/enroll.php';
		$JsTemplateUrl = JS_URL . '/voucher_purchase_enroll.js';

		$dealingsState = '거래대기';
		$dealingsType = '구매';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// 판매물품 조회
        $rSellItemP = [
            'is_sell'=> 'Y'
        ];

        $rSellItemQ = 'SELECT `idx`,
                              `item_name`,
                              `commission` 
					   FROM `th_sell_item` 
					   WHERE `is_sell` = ?';

        $rSellItemResult = $db->execute($rSellItemQ, $rSellItemP);
        if ($rSellItemResult === false) {
            throw new Exception('판매물품을 조회하면서 오류가 발생했습니다.');
        }

        $rSellItemResultCount = $rSellItemResult->recordCount();

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/voucher_purchase_enroll.html.php';
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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_voucher.html.php';