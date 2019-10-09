<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 상품권 거래 취소 (판매/구매)
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

    // Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/CouponClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/VoucherClass.php';

	// Exception 파일 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$returnUrl = SITE_ADMIN_DOMAIN; // 리턴되는 화면 URL 초기화.
        $alertMessage = '';

		if ($connection === false) {
           throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$couponClass = new CouponClass($db);
		$voucherClass = new VoucherClass($db);

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
		if ($resultVoucherData['isValid'] == false) {
			// 유효성 오류
			throw new Exception($resultVoucherData['errorMessage']);
		}

		$db->startTrans();

		$voucherName = $postData['voucher_name']; 

		$itemIdx = $voucherClass->getVoucherItemIdx($voucherName);
		if ($itemIdx === false) {
			throw new RollbackException("상품권 고유번호를 가져오면서 문제가 발생했습니다.");
		}

		if(empty($itemIdx)) {
			throw new RollbackException("상품권 고유번호가 존재하지 않습니다.");
		}

		if ($postData['voucher_price'] > 0) {
			$discountMileage = round(($postData['voucher_price']*$postData['discount_rate'])/100);
		} else {
			$discountMileage = 0;
		}

		$insertData = [
			'issue_type'=> $postData['coupon_issue_type'],
			'sell_item_idx'=> $itemIdx,
			'subject'=> $postData['coupon_subject'],
			'item_monmey'=> $postData['voucher_price'],
			'discount_rate'=> $postData['discount_rate'],
			'discount_mileage'=> $discountMileage,
			'start_date'=> $postData['start_date'],
			'expiration_date'=> $postData['expiration_date']
		];

		$insertResult = $couponClass->insertCupon($insertData);
		if($insertResult < 1){
			throw new RollbackException('쿠폰 생성중에 문제가 발생하였습니다.');
		}
		$alertMessage = '정상적으로 등록되었습니다.';

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