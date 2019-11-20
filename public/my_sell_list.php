<?php
	/**
	 * 나의 판매 등록 현황
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
		$title = TITLE_VOUCHER_SELL_ENROLL_STATUS . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN.'/voucher_dealings.php';

        $alertMessage = '';
		$dealingsType = '판매';

		$memberIdx = $_SESSION['idx'];

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// 나의 판매 등록글 조회
        $rSellListP = [
            'dealings_type'=> $dealingsType,
            'is_del'=> 'N',
            'writer_idx'=> $memberIdx
        ];

		$rSellListQ = 'SELECT `idx`,
                              `dealings_subject`,
                              `item_money`,
                              `item_no`,
                              `item_object_no`,
                              `dealings_mileage`,
                              `dealings_status`,
                              `writer_idx`
                       FROM `th_dealings`
                       WHERE `dealings_type` = ?
					   AND `is_del` = ?
					   AND `writer_idx` = ?
					   ORDER BY `register_date` DESC, `dealings_subject` ASC, `dealings_status` DESC';

        $rSellListResult = $db->execute($rSellListQ, $rSellListP);
        if ($rSellListResult === false) {
            throw new Exception('판매 내역을 조회하면서 오류가 발생했습니다.');
        }

        $mySellData = [];

        // 판매상세글 화면으로 이동
        $dealingsDetailViewHref = SITE_DOMAIN . '/my_sell_dealings_status.php';

        foreach ($rSellListResult as $key => $value) {
            // 판매상세글 화면으로 이동
            $dealingsDetailViewHref = SITE_DOMAIN . '/my_sell_dealings_status.php';

            $dealingsIdx = $value['idx'];
            $dealingsSubject = $value['dealings_subject'];
            $itemMoney = $value['item_money'];
            $itemNo =  $value['item_no'];
            $itemObjectNo = $value['item_object_no'];
            $dealingsMileage = $value['dealings_mileage'];
            $dealingsStatus = $value['dealings_status'];
            $writerIdx = $value['writer_idx'];

            // 판매자 상세정보 조회
            $rSellerQ = 'SELECT `id`,
                                `name`
                         FROM `th_members`
                         WHERE `idx` = ?';

            $rSellerResult = $db->execute($rSellerQ, $writerIdx);
            if ($rSellerResult === false) {
                throw new Exception('판매자를 조회하면서 오류가 발생했습니다.');
            }

            $sellerId = $rSellerResult->fields['id'];
            $sellerName = $rSellerResult->fields['name'];

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

            // 거래 유저 조회 (구매자)
            $rDealingsUserQ = 'SELECT `dealings_member_idx`,
                                      `dealings_date`
                               FROM `th_dealings_user`
                               WHERE `dealings_idx` = ?';

            $rDealingsUserResult = $db->execute($rDealingsUserQ, $dealingsIdx);
            if ($rDealingsUserResult === false) {
                throw new Exception('구매자 키를 조회하면서 오류가 발생했습니다.');
            }

            $purchaserIdx = $rDealingsUserResult->fields['dealings_member_idx'];
            $dealingsDate = $rDealingsUserResult->fields['dealings_date'];

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

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/my_sell_list.html.php';
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