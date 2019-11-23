<?php
	/**
	 * 판매 거래 등록 화면
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
		$title = TITLE_VOUCHER_SELL_ENROLL . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN . '/voucher_dealings.php';

		$alertMessage = '';

		$actionUrl = DEALINGS_PROCESS_ACCTION . '/sell_enroll.php';
		$JsTemplateUrl = JS_URL . '/voucher_sell_enroll.js';
		$dealingsState = '거래대기';
		$dealingsType = '판매';

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

        $rSellItemCount = $rSellItemResult->recordCount();

        $itemNo = $itemMoney = $subject = $content = $itemObjectNo = $mileage = $memo = '';

        // 쿠폰 선택 시 submit이 되는 경우 다시 받을 것
        if (count($_POST) > 0) {
            // xss 방지
            $_POST['item_no'] = htmlspecialchars($_POST['item_no']);
            $_POST['item_money'] = htmlspecialchars($_POST['item_money']);
            $_POST['dealings_subject'] = htmlspecialchars($_POST['dealings_subject']);
            $_POST['dealings_content'] = htmlspecialchars($_POST['dealings_content']);
            $_POST['item_object_no'] = htmlspecialchars($_POST['item_object_no']);
            $_POST['dealings_mileage'] = htmlspecialchars($_POST['dealings_mileage']);
            $_POST['memo'] = htmlspecialchars($_POST['memo']);
            $postData = $_POST;

            // $_POST 전역 변수가 아니라 일반 변수에 담기
            $itemNo = $_POST['item_no'];
            $itemMoney = $_POST['item_money'];
            $subject = $_POST['dealings_subject'];
            $content = $_POST['dealings_content'];
            $itemObjectNo = $_POST['item_object_no'];
            $mileage = $_POST['dealings_mileage'];
            $memo = $_POST['memo'];
        }

        // 이용가능한 쿠폰 조회
        $rAvailableCouponP = [
            'sell_item_idx'=> $itemNo,
            'issue_type'=> '판매',
            'item_money'=> $itemMoney,
            'is_coupon_del'=> 'N',
            'is_del'=> 'N',
            'member_idx'=> $_SESSION['idx'],
            'coupon_status'=> 1
        ];

        $rAvailableCouponQ = 'SELECT `idx`,
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

        $rAvailableCouponResult = $db->execute($rAvailableCouponQ, $rAvailableCouponP);
        if ($rAvailableCouponResult === false) {
            throw new Exception('사용 가능한 쿠폰을 조회하면서 오류가 발생했습니다.');
        }

        $couponDataCount = $rAvailableCouponResult->recordCount();

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/voucher_sell_enroll.html.php';
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