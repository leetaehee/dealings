<?php
	/**
	 * 관리자가 쿠폰을 발행.
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

    // Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/CouponClass.php';

	// Exception 파일 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$returnUrl = SITE_ADMIN_DOMAIN;
        $alertMessage = '';

		if ($connection === false) {
           throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$couponClass = new CouponClass($db);

		// injection, xss 방지코드
		$_POST['coupon_subject'] = htmlspecialchars($_POST['coupon_subject']);
		$_POST['voucher_name'] = htmlspecialchars($_POST['voucher_name']);
		$_POST['voucher_price'] = htmlspecialchars($_POST['voucher_price']);
		$_POST['discount_rate'] = htmlspecialchars($_POST['discount_rate']);
		$_POST['coupon_issue_type'] = htmlspecialchars($_POST['coupon_issue_type']);
		$_POST['start_date'] = htmlspecialchars($_POST['start_date']);
		$_POST['expiration_date'] = htmlspecialchars($_POST['expiration_date']);
		$postData = $_POST;

		// returnURL
		$returnUrl = SITE_ADMIN_DOMAIN . '/coupon.php';

		$resultCouponValidCheck = $couponClass->checkCouponFormValidate($postData);
		if ($resultCouponValidCheck['isValid'] == false) {
			// 유효성 오류
			throw new Exception($resultCouponValidCheck['errorMessage']);
		}

		$voucherData = [
			'voucher_array' => $CONFIG_COUPON_VOUCHER_ARRAY,
			'money_array' => $CONFIG_VOUCHER_MONEY_ARRAY ,
			'voucher_name' => $postData['voucher_name'],
			'voucher_price' => $postData['voucher_price'],
			'coupon_issue_array' => $CONFIG_COUPON_ISSUE_TYPE,
			'coupon_issue_type' => $postData['coupon_issue_type']
		];

		$resultVoucherData = $couponClass->checkVoucherValidate($voucherData);
		if ($resultVoucherData['isValid'] === false) {
			throw new Exception($resultVoucherData['errorMessage']);
		}

		$db->startTrans();

		// 판매 물품 고유 키 가져오기
        $voucher_name = $postData['voucher_name'];

        $rSellItemQ = 'SELECT `idx` 
                       FROM `th_sell_item` 
                       WHERE `item_name` = ?
                       FOR UPDATE';

        $rSellItemResult = $db->execute($rSellItemQ, $voucher_name);
        if ($rSellItemResult === false) {
            throw new RollbackException('판매 물품 고유 정보를 조회하면서 오류가 발생했습니다.');
        }

        $sellItemIdx = $rSellItemResult->fields['idx'];
        if (empty($sellItemIdx)) {
            throw new RollbackException('판매 물품 고유 번호가 존재하지 않습니다.');
        }

        // 판매 물품에 할인된 금액 구하기
        if ($postData['voucher_price'] > 0) {
            $discountMileage = round(($postData['voucher_price']*$postData['discount_rate'])/100);
        } else {
            $discountMileage = 0;
        }

        // 쿠폰 발행하기
        $cCouponP = [
            'issue_type'=> $postData['coupon_issue_type'],
            'sell_item_idx'=> $sellItemIdx,
            'subject'=> $postData['coupon_subject'],
            'item_monmey'=> $postData['voucher_price'],
            'discount_rate'=> $postData['discount_rate'],
            'discount_mileage'=> $discountMileage,
            'start_date'=> $postData['start_date'],
            'expiration_date'=> $postData['expiration_date']
        ];

        $cCouponQ = 'INSERT INTO `th_coupon` SET 
					  `issue_type` = ?,
					  `sell_item_idx` = ?,
					  `subject` = ?,
					  `item_money` = ?,
					  `discount_rate` = ?,
					  `discount_mileage` = ?,
					  `start_date` = ?,
					  `expiration_date` = ?,
					  `issue_date` = CURDATE()';

        $cCouponResult = $db->execute($cCouponQ, $cCouponP);

        $couponInsertId = $db->insert_id();
        if ($couponInsertId < 1) {
            exit;
            throw new RollbackException('쿠폰을 발행하는 중에 오류가 발생했습니다.');
        }

		$returnUrl = SITE_ADMIN_DOMAIN . '/coupon_issue_list.php';

		$alertMessage = '정상적으로 쿠폰이 발행되었습니다.';

		$db->completeTrans();
	} catch (RollbackException $e) {
		// 트랜잭션 문제가 발생했을 때
		$alertMessage = $e->getMessage();

		$db->failTrans();
		$db->completeTrans();
	} catch (Exception $e) {
		// 트랜잭션을 사용하지 않을 때
		$alertMessage = $e->getMessage();
    } finally {
        if  ($connection === true) {
            $db->close();
        }
        if (!empty($alertMessage)) {
            alertMsg($returnUrl, 1, $alertMessage);
        } else {
            alertMsg(SITE_ADMIN_DOMAIN, 0);
        }
    }