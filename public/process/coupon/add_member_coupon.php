<?php
	/**
	 * 회원에게 쿠폰 지급 
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
		$returnUrl = SITE_ADMIN_DOMAIN; // 리턴되는 화면 URL 초기화.
        $alertMessage = '';

		if ($connection === false) {
           throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$couponClass = new CouponClass($db);

		// injection, xss 방지코드
		$_GET['member_idx'] = htmlspecialchars($_GET['member_idx']);
		$_GET['coupon_idx'] = htmlspecialchars($_GET['coupon_idx']);
		$getData = $_GET;

		// returnURL
		$returnUrl = SITE_ADMIN_DOMAIN . '/coupon.php';

		if (empty($getData['member_idx'])) {
			throw new Exception('비정상적인 접근입니다.'); 
		}

		$db->startTrans();

        $couponIdx = $getData['coupon_idx'];

        // 쿠폰 지급
        $cCouponProvideP = [
            'coupon_idx'=> $couponIdx,
            'member_idx'=> $getData['member_idx'],
            'is_coupon_del'=> 'N',
            'is_del'=> 'N',
            'coupon_status'=> 1
        ];

        $couponProvideResult = $couponClass->couponProvideProcess($cCouponProvideP);
        if ($couponProvideResult['result'] === false) {
            throw new RollbackException($couponProvideResult['resultMessage']);
        }

        $returnUrl = SITE_ADMIN_DOMAIN . '/coupon_member_status.php';

		$alertMessage = '정상적으로 쿠폰이 지급이 되었습니다.';

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