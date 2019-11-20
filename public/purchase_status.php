<?php
	/**
	 * 판매중인 물품 리스트
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
		$title = TITLE_VOUCHER_PURCHASE_STATUS . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN.'/voucher_dealings.php';

		$alertMessage = '';
		$dealingsType = '구매';

		$memberIdx = $_SESSION['idx'];

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

        $rSellListP = [
            'dealings_type'=> $dealingsType,
            'dealings_status'=> 1,
            'dealings_member_idx'=> $memberIdx
        ];

		$rSellListQ = 'SELECT `dealings_idx`,
							  `dealings_status`,
							  `dealings_date`,
							  `dealings_member_idx`
					   FROM `th_dealings_user`
					   WHERE `dealings_type` = ?
					   AND `dealings_status` <> ?
					   AND `dealings_member_idx` = ?
					   ORDER BY `dealings_date` DESC, `dealings_status` ASC';

        $rSellListResult = $db->execute($rSellListQ, $rSellListP);
        if ($rSellListResult === false) {
            throw new Exception('판매 내역을 조회하면서 오류가 발생했습니다.');
        }

        $mySellData = [];

        foreach ($rSellListResult as $key => $value) {
            // 판매상세글 화면으로 이동
            $dealingsDetailViewHref = SITE_DOMAIN . '/dealings_purchase_status_view.php';

            $dealingsIdx = $value['dealings_idx'];
            $dealingsStatus = $value['dealings_status'];
            $dealingsMemberIdx = $value['dealings_member_idx'];
            $dealingsDate = $value['dealings_date'];

            // 판매자 상세정보 조회
            $rSellerQ = 'SELECT `id`,
                                `name`
                         FROM `th_members`
                         WHERE `idx` = ?';

            $rSellerResult = $db->execute($rSellerQ, $dealingsMemberIdx);
            if ($rSellerResult === false) {
                throw new Exception('판매자를 조회하면서 오류가 발생했습니다.');
            }

            $sellerId = $rSellerResult->fields['id'];
            $sellerName = $rSellerResult->fields['name'];

            $rSellListQ = 'SELECT `dealings_subject`,
                                  `item_money`,
                                  `item_no`,
                                  `item_object_no`,
                                  `dealings_mileage`,
                                  `writer_idx`
                           FROM `th_dealings`
                           WHERE `idx` = ?';

            $rSellListResult = $db->execute($rSellListQ, $dealingsIdx);
            if ($rSellListResult === false) {
                throw new Exception('거래 정보를 조회하면서 오류가 발생했습니다.');
            }

            $dealingsSubject = $rSellListResult->fields['dealings_subject'];
            $itemMoney = $rSellListResult->fields['item_money'];
            $itemNo =  $rSellListResult->fields['item_no'];
            $itemObjectNo = $rSellListResult->fields['item_object_no'];
            $dealingsMileage = $rSellListResult->fields['dealings_mileage'];
            $writerIdx = $rSellListResult->fields['writer_idx'];

            // 판매 물품명 조회
            $rSellItemQ = 'SELECT `item_name`
                           FROM `th_sell_item`
                           WHERE `idx` = ?';

            $rSellItemResult = $db->execute($rSellItemQ, $itemNo);
            if ($rSellItemResult === false) {
                throw Exception('판매 물품 이름을 조회하면서 오류가 발생했습니다.');
            }

            $itemName = $rSellItemResult->fields['item_name'];

            // 거래 상태 조회
            $rDealingsStatusQ = 'SELECT `dealings_status_name`
                                 FROM `th_dealings_status_code`
                                 WHERE `idx` = ?';

            $rDealingsStatusResult = $db->execute($rDealingsStatusQ, $dealingsStatus);
            if ($rDealingsStatusResult === false) {
                throw new Exception('거래 상태를 조회하면서 오류가 발생했습니다.');
            }

            $dealingsStatusName = $rDealingsStatusResult->fields['dealings_status_name'];

            // 구매자 조회 거래글 작성자로 조회- 구매자 정보 조회)
            $rPurchaserQ = 'SELECT `id`,
                                   `name`
                            FROM `th_members`
                            WHERE `idx` = ?';

            $rPurchaserResult = $db->execute($rPurchaserQ, $writerIdx);
            if ($rPurchaserResult === false) {
                throw new Exception('구매자를 조회하면서 오류가 발생했습니다.');
            }

            $purchaserId = $rSellerResult->fields['id'];
            $purchaserName = $rSellerResult->fields['name'];


            // 판매 상세 화면으로 이동하기 위한 파라미터
            $dealingsDetailViewHref .= '?type=' . $dealingsStatus . '&idx=' . $dealingsIdx;

            // 화면에 보여주어야 할 데이터 배열에 저장.
            $mySellData[] = [
                'seq'=> $key+1,
                'dealings_idx'=> $dealingsIdx,
                'dealings_subject'=> $dealingsSubject,
                'item_money'=> $itemMoney,
                'item_no'=> $itemNo,
                'item_object_no'=> $itemObjectNo,
                'dealings_mileage'=> $dealingsMileage,
                'dealings_status'=> $dealingsStatus,
                'dealings_date'=> $dealingsDate,
                'item_name'=> $itemName,
                'dealings_status_name'=> $dealingsStatusName,
                'detail_view_url'=> $dealingsDetailViewHref
            ];
        }

        $mySellDataCount = count($mySellData);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/purchase_status.html.php';
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