<?php
	/**
	 * 마이룸 > 판매 > 판매중인물품 > 판매취소 버튼 클릭 시 기능 정의
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

    // Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/DealingsClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/CouponClass.php';

	// Exception 파일 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화.
        $alertMessage = '';

		if ($connection === false) {
           throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$dealingsClass = new DealingsClass($db);
		$couponClass = new CouponClass($db);

		$_POST['dealings_type'] = htmlspecialchars($_POST['dealings_type']);
		$postData = $_POST;

		// returnURL
		$returnUrl = SITE_DOMAIN . '/mypage.php';

		$dealingsTypeArray = ['구매', '판매'];
		if (!in_array($postData['dealings_type'], $dealingsTypeArray)) {
			throw new Exception('유효하지 않은 거래 타입입니다. 다시 시도하세요.');
		}

		$db->startTrans();

		// 거래 상태 파라미터
		$uDealingsStatusP = [
			'dealings_status'=> 1,
			'dealings_idx'=> $_SESSION['dealings_idx']
		];

		// 거래상태 관련
		$dealingsProcessResult = $dealingsClass->dealignsStatusProcess($uDealingsStatusP);
		if ($dealingsProcessResult['result'] === false) {
			throw new RollbackException($dealingsProcessResult['resultMessage']);
		}

		// 쿠폰 복구
		$couponStatusP = [
			'dealings_idx'=> $_SESSION['dealings_idx'],
			'member_idx'=> $_SESSION['idx'],
			'issue_type'=> '판매',
			'coupon_use_end_date'=> $today,
			'is_refund'=> 'N'
		];

		$couponRefundResult = $couponClass->couponRefundProcess($couponStatusP);
		if ($couponRefundResult['result'] === false) {
			throw new RollbackException($couponRefundResult['resultMessage']);
		}

		$returnUrl = SITE_DOMAIN.'/mypage.php';
		$alertMessage = '판매가 취소되었습니다!';

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
            alertMsg(SITE_DOMAIN, 0);
        }
    }