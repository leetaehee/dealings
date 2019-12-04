<?php
	/**
	 * 회원 거래관리 현황
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
		$title = TITLE_ADMIN_DEALINGS_STATUS . ' | ' . SITE_ADMIN_DOMAIN;
		$returnUrl = SITE_ADMIN_DOMAIN . '/admin_page.php';

		$alertMessage = '';

		$dealingsType = '구매';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// 거래 대상 조회
        $rDealingsUserQ = 'SELECT `dealings_idx`,
                                  `dealings_status`,
                                  `dealings_date`,
                                  `dealings_writer_idx`,
                                  `dealings_member_idx`,
                                  `dealings_type`,
                                  `dealings_status`
                           FROM `th_dealings_user`
                           WHERE `dealings_status` IN (3,4,5)
                           ORDER BY `dealings_date` DESC, `dealings_idx` ASC';

        $rDealingsUserResult = $db->execute($rDealingsUserQ);
        if ($rDealingsUserResult === false) {
            throw new Exception('거래 대상을 조회하면서 오류가 발생했습니다.');
        }

        // 거래내역 추가
        $dealingsUserData = [];

        foreach ($rDealingsUserResult as $key => $value) {
            $dealingsIdx = $value['dealings_idx'];
            $dealingsStatus = $value['dealings_status'];
            $dealingsDate = $value['dealings_date'];
            $dealingsWriterIdx = $value['dealings_writer_idx'];
            $dealingsMemberIdx = $value['dealings_member_idx'];
            $dealingsType = $value['dealings_type'];

            // 거래 정보 조회
            $rDealingsQ = 'SELECT `dealings_subject`,
                                  `item_money`,
                                  `dealings_mileage`,
                                  `item_no`
                           FROM `th_dealings`
                           WHERE `idx` = ?';

            $rDealingsResult = $db->execute($rDealingsQ, $dealingsIdx);
            if ($rDealingsResult === false) {
                throw new Exception('거래 정보를 조회하면서 오류가 발생했습니다.');
            }

            $dealingsSubject = $rDealingsResult->fields['dealings_subject'];
            $itemMoney = $rDealingsResult->fields['item_money'];
            $dealingsMileage = $rDealingsResult->fields['dealings_mileage'];
            $itemNo = $rDealingsResult->fields['item_no'];

            // 거래물품 조회
            $rSellItemQ = 'SELECT `item_name`
                           FROM `th_sell_item`
                           WHERE `idx` = ?';

            $rSellItemResult = $db->execute($rSellItemQ, $itemNo);
            if ($rSellItemResult === false) {
                throw new Exception('거래 물품명을 조회하면서 오류가 발생했습니다,');
            }

            $itemName = $rSellItemResult->fields['item_name'];

            // 거래 상태명 조회
            $rDealingsStatusQ = 'SELECT `dealings_status_name`
                                 FROM `th_dealings_status_code`
                                 WHERE `idx` = ?';

            $rDealingsStatusResult = $db->execute($rDealingsStatusQ, $dealingsStatus);
            if ($rDealingsStatusResult === false) {
                throw new Exception('거래 상태명을 조회하면서 오류가 발생했습니다.');
            }

            $dealingsStatusName = $rDealingsStatusResult->fields['dealings_status_name'];

            // 거래글 작성자 정보 조회
            $rWriterQ = 'SELECT `id`,
                                `name`
                         FROM `th_members`
                         WHERE `idx` = ?';

            $rWriterResult = $db->execute($rWriterQ, $dealingsWriterIdx);
            if ($rWriterResult === false) {
                throw new Exception('거래글 작성자 정보를 조회하면서 오류가 발생했습니다.');
            }

            $id = $rWriterResult->fields['id'];
            $name = $rWriterResult->fields['name'];

            // 거래자 정보 조회
            $rDealingsUserQ = 'SELECT `id`,
                                      `name`
                               FROM `th_members`
                               WHERE `idx` = ?';

            $rDealingsUserResult = $db->execute($rWriterQ, $dealingsMemberIdx);
            if ($rDealingsUserResult === false) {
                throw new Exception('거래자 정보를 조회하면서 오류가 발생했습니다.');
            }

            $dealingsUserId = $rDealingsUserResult->fields['id'];
            $dealingsUserName = $rDealingsUserResult->fields['name'];

            $dealingsUserData[] = [
                'seq'=> ($key+1),
                'dealings_idx'=> $dealingsIdx,
                'dealings_status'=> $dealingsStatus,
                'dealings_date'=> $dealingsDate,
                'dealings_writer_idx'=> $dealingsWriterIdx,
                'dealings_member_idx'=> $dealingsMemberIdx,
                'dealings_type'=> $dealingsType,
                'dealings_subject'=> $dealingsSubject,
                'item_money'=> $itemMoney,
                'dealings_mileage'=> number_format($dealingsMileage),
                'item_name'=> $itemName,
                'dealings_status_name'=> $dealingsStatusName,
                'id'=> $id,
                'name'=> setDecrypt($name),
                'dealings_user_name'=> setDecrypt($dealingsUserName)
            ];
        }

        $dealingsUserDataCount = count($dealingsUserData);

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/dealings_manage.html.php';
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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_main.html.php';// 전체 레이아웃