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
		$title = TITLE_VOUCHER_SELL_STATUS . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN.'/voucher_dealings.php';

		$alertMessage = '';
		$dealingsType = '판매';

		$memberIdx = $_SESSION['idx'];

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$rPurchaseUserListP = [
            'dealings_type'=> $dealingsType,
            'dealings_status'=> 1,
            'dealings_member_idx'=> $memberIdx,
        ];

        $rPurchaseUserListQ = 'SELECT `dealings_idx`,
                                      `dealings_status`,
                                      `dealings_date`,
                                      `dealings_member_idx`
                               FROM `th_dealings_user`
                               WHERE `dealings_type` = ?
                               AND `dealings_status` <> ?
                               AND `dealings_member_idx` = ?
                               ORDER BY `dealings_date` DESC, `dealings_status` ASC';

        $rPurchaseUserResult = $db->execute($rPurchaseUserListQ, $rPurchaseUserListP);
        if ($rPurchaseUserResult === false) {
            throw new Exception('구매 내역을 조회하면서 오류가 발생했습니다.');
        }

        $purChaseData = [];

        foreach ($rPurchaseUserResult as $key => $value) {
            // 구매상세글 화면으로 이동
            $dealingsDetailViewHref = SITE_DOMAIN . '/dealings_sell_status_view.php';

            $dealingsIdx = $value['dealings_idx'];
            $dealingsStatus = $value['dealings_status'];
            $dealingsMemberIdx = $value['dealings_member_idx'];
            $dealingsDate = $value['dealings_date'];

            // 구매자 상세정보 조회
            $rPurchaserQ = 'SELECT `id`,
                                   `name`
                            FROM `th_members`
                            WHERE `idx` = ?';

            $rPurchaserResult = $db->execute($rPurchaserQ, $dealingsMemberIdx);
            if ($rPurchaserResult === false) {
                throw new Exception('구매자를 조회하면서 오류가 발생했습니다.');
            }

            $purchaserId = $rPurchaserResult->fields['id'];
            $purchaserName = $rPurchaserResult->fields['name'];

            // 거래 정보 조회
            $rPurchaseDataQ = 'SELECT `dealings_subject`,
                                  `item_money`,
                                  `item_no`,
                                  `dealings_mileage`,
                                  `writer_idx`
                           FROM `th_dealings`
                           WHERE `idx` = ?';

            $rPurchaseDataResult = $db->execute($rPurchaseDataQ, $dealingsIdx);
            if ($rPurchaseDataResult === false) {
                throw new Exception('거래 정보를 조회하면서 오류가 발생했습니다.');
            }

            $dealingsSubject = $rPurchaseDataResult->fields['dealings_subject'];
            $itemMoney = $rPurchaseDataResult->fields['item_money'];
            $itemNo =  $rPurchaseDataResult->fields['item_no'];
            $dealingsMileage = $rPurchaseDataResult->fields['dealings_mileage'];
            $writerIdx = $rPurchaseDataResult->fields['writer_idx'];

            // 구매 물품명 조회
            $rSellItemQ = 'SELECT `item_name`
                           FROM `th_sell_item`
                           WHERE `idx` = ?';

            $rSellItemResult = $db->execute($rSellItemQ, $itemNo);
            if ($rSellItemResult === false) {
                throw Exception('구매 물품 이름을 조회하면서 오류가 발생했습니다.');
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

            // 판매자 조회 거래글 작성자로 조회- 판매자 정보 조회)
            $rSellerQ = 'SELECT `id`,
                                   `name`
                            FROM `th_members`
                            WHERE `idx` = ?';

            $rSellerResult = $db->execute($rPurchaserQ, $writerIdx);
            if ($rSellerResult === false) {
                throw new Exception('판매자를 조회하면서 오류가 발생했습니다.');
            }

            $sellerId = $rSellerResult->fields['id'];
            $sellerName = $rSellerResult->fields['name'];

            // 구매 상세글에 파라미터 전달
            $dealingsDetailViewHref .= '?type=' . $dealingsStatus . '&idx=' . $dealingsIdx;

            // 화면에 보여주어야 할 데이터 배열에 저장.
            $purChaseData[] = [
                'seq'=> $key+1,
                'dealings_idx'=> $dealingsIdx,
                'dealings_subject'=> $dealingsSubject,
                'item_money'=> $itemMoney,
                'item_no'=> $itemNo,
                'dealings_mileage'=> $dealingsMileage,
                'dealings_status'=> $dealingsStatus,
                'dealings_date'=> $dealingsDate,
                'item_name'=> $itemName,
                'dealings_status_name'=> $dealingsStatusName,
                'detail_view_url'=> $dealingsDetailViewHref
            ];
        }

        $purChaseDataCount = count($purChaseData);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/sell_status.html.php';
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