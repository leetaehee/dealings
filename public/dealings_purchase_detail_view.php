<?php
	/**
	 * 상품권거래 > 거래하기 > 상품권 구매목록에서 판매하기 화면
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
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_VOUCHER_PURCHASE_DETAIL_VIEW . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN.'/voucher_purchase_list.php';
		$alertMessage = '';

		$actionUrl = DEALINGS_PROCESS_ACCTION . '/changeSellStatus.php';
		$JsTemplateUrl = JS_URL . '/dealings_purchase_detail_view.js';
		$dealingsType = '구매';
		$btnName = '판매하기';

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

        // 거래유저정보 조회
        $rDealingsUserQ = 'SELECT `dealings_member_idx`
                           FROM `th_dealings_user`
                           WHERE `dealings_idx` = ?';

        $rDealingsUserResult = $db->execute($rDealingsUserQ, $dealingsIdx);
        if ($rDealingsUserResult === false) {
            throw new RollbackException('거래 유저를 조회하면서 오류가 발생햇습니다.');
        }

        $dealingsMemberIdx = $rDealingsUserResult->fields['dealings_member_idx'];

        // 거래 상태명 가져오기
        $rDealingsStatusQ = 'SELECT `dealings_status_name`
                             FROM `th_dealings_status_code`
                             WHERE `idx` = ?';

        $rDealingsStatusResult = $db->execute($rDealingsStatusQ, $dealingsStatus);
        if ($rDealingsStatusResult === false) {
            throw new RollbackException('거래 상태이름을 조회하면서 오류가 발생했습니다.');
        }

        $dealingsStatusName = $rDealingsStatusResult->fields['dealings_status_name'];

        // 이용가능한 쿠폰 가져오기
        $rCouponUseWaitP = [
            'sell_item_idx'=> $itemNo ?? '',
            'issue_type'=> '판매',
            'item_money'=> $itemMoney ?? '',
            'is_coupon_del'=> 'N',
            'is_del'=> 'N',
            'member_idx'=> $_SESSION['idx'],
            'coupon_status'=> 1
        ];

        $rCouponUseWaitQ = 'SELECT `idx`,
                                   `subject`,
                                   `item_money`,
                                   `discount_rate`
                            FROM `th_coupon_member`
                            WHERE `sell_item_idx` IN (?,5)  
                            AND `issue_type` = ?
                            AND `item_money` IN (?,0)
                            AND `is_coupon_del` = ?
                            AND `is_del` = ?
                            AND `member_idx` = ?
                            AND `coupon_status` = ?';

        $rCouponUseWaitResult = $db->execute($rCouponUseWaitQ, $rCouponUseWaitP);
        if ($rCouponUseWaitResult === false) {
            throw new RollbackException('지급된 사용 가능 쿠폰을 조회하면서 오류가 발생했습니다.');
        }

        $couponUseCount = $rCouponUseWaitResult->recordCount();

        // 세션정보 추가
        $_SESSION['dealings_writer_idx'] = $writerIdx;
        $_SESSION['dealings_idx'] = $dealingsIdx;
        $_SESSION['dealings_status'] = $getData['type'];
        $_SESSION['dealings_mileage'] = $dealingsMileage;

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/dealings_purchase_detail_view.html.php';
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