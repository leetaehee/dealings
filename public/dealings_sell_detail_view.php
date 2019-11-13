<?php
	/*
	 * 판매 거래 상세 화면 
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
		$title = TITLE_VOUCHER_SELL_DETAIL_VIEW . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN.'/voucher_purchase_list.php'; // 리턴되는 화면 URL 초기화
		$alertMessage = '';

		$actionUrl = DEALINGS_PROCESS_ACCTION . '/changeStatus.php';
		$JsTemplateUrl = JS_URL . '/dealings_sell_detail_view.js';
		$dealingsType = '판매';
		$btnName = '구매하기';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// xss, injection 방지
		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$_GET['type'] = htmlspecialchars($_GET['type']);
		$getData = $_GET;

        // 거래테이블 고유키
        $dealingsIdx = $getData['idx'];

        // 거래정보 가져오기
        $rDealingsQ = 'SELECT `idx`,
							  `dealings_type`,
							  `register_date`,
							  `expiration_date`,
							  `dealings_subject`,
							  `dealings_content`,
							  `item_money`,
							  `item_no`,
							  `item_object_no`,
							  `dealings_mileage`,
							  `dealings_commission`,
							  `dealings_status`,
							  `writer_idx`,
							  `memo`
					    FROM `th_dealings`
					    WHERE `idx` = ?';

        $rDealingsResult = $db->execute($rDealingsQ, $dealingsIdx);
        if ($rDealingsResult === false) {
            throw new RollbackException('거래 데이터를 조회하면서 오류가 발생했습니다.');
        }

        $writerIdx = $rDealingsResult->fields['writer_idx'];
        $itemNo = $rDealingsResult->fields['item_no'];
        $dealingsStatus = $rDealingsResult->fields['dealings_status'];
        $registerDate = $rDealingsResult->fields['register_date'];
        $expirationDate = $rDealingsResult->fields['expiration_date'];
        $dealingsSubject = $rDealingsResult->fields['dealings_subject'];
        $dealingsContent = $rDealingsResult->fields['dealings_content'];
        $itemMoney = $rDealingsResult->fields['item_money'];
        $itemObjectNo = $rDealingsResult->fields['item_object_no'];
        $dealingsMileage = $rDealingsResult->fields['dealings_mileage'];
        $dealingsCommission = $rDealingsResult->fields['dealings_commission'];
        $memo = $rDealingsResult->fields['memo'];

        // 구매자 정보 가져오기
        $rPurchaserQ = 'SELECT `name`,
							   `id`
						FROM `th_members`
						WHERE `idx` = ?';

        $rPurchaserResult = $db->execute($rPurchaserQ, $writerIdx);
        if ($rPurchaserResult === false) {
            throw new RollbackException('구매자 정보를 조회하면서 오류가 발생했습니다.');
        }

        $name = $rPurchaserResult->fields['name'];
        $id = $rPurchaserResult->fields['id'];

        // 상품권명 가져오기
        $rSellItemQ = 'SELECT `item_name`
                       FROM `th_sell_item`
                       WHERE `idx` = ?';

        $rSellItemResult = $db->execute($rSellItemQ, $itemNo);
        if ($rSellItemResult === false) {
            throw new RollbackException('상품권명을 조회하면서 오류가 발생했습니다.');
        }

        $itemName = $rSellItemResult->fields['item_name'];

        // 거래 상태명 가져오기
        $rDealingsStatusQ = 'SELECT `dealings_status_name`
                             FROM `th_dealings_status_code`
                             WHERE `idx` = ?';

        $rDealingsStatusResult = $db->execute($rDealingsStatusQ, $dealingsStatus);
        if ($rDealingsStatusResult === false) {
            throw new RollbackException('거래 상태이름을 조회하면서 오류가 발생했습니다.');
        }

        $dealingsStatusName = $rDealingsStatusResult->fields['dealings_status_name'];

        $_SESSION['dealings_writer_idx'] = $writerIdx;
        $_SESSION['dealings_idx'] = $dealingsIdx;
        $_SESSION['dealings_status'] = $getData['type'];

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/dealings_sell_detail_view.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_voucher.html.php'; // 전체 레이아웃