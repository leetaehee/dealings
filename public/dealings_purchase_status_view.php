<?php
	/**
	 * 판매중인물품 
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php';
	
	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	// Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/DealingsClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/CouponClass.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_VOUCHER_PURCHASE_STATUS . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN.'/mypage.php'; // 리턴되는 화면 URL 초기화
		$alertMessage = '';

		$actionUrl = DEALINGS_PROCESS_ACCTION . '/changeCancelStatus.php';
		$JsTemplateUrl = JS_URL . '/dealings_purchase_status_view.js';
		$dealingsType = '판매';
		$btnName = '판매취소';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// xss, injection 방지
		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$_GET['type'] = htmlspecialchars($_GET['type']);
		$getData = $_GET;

		// 디비에서 거래상태받아오기, 거래타입과 키값 보내기
		$dealingsClass = new DealingsClass($db);
		$couponClass = new CouponClass($db);

		$dealingsData = $dealingsClass->getDealingsData($getData['idx']);
		if ($dealingsData === false) {
			throw new Exception('회원 판매 거래정보 가져오는데 오류가 발생했습니다.');
		}
		$commission = $dealingsData->fields['dealings_commission'];

		$_SESSION['dealings_writer_idx'] = $dealingsData->fields['writer_idx'];
		$_SESSION['dealings_idx'] = $getData['idx'];
		$_SESSION['dealings_status'] = $getData['type'];

		// 사용한 쿠폰정보 가져오기
		$useCouponParam = [
			'dealings_idx'=> $getData['idx'],
			'member_idx'=> $_SESSION['idx'],
			'issue_type'=> '판매',
			'is_refund'=> 'N'
		];

		$useCouponData = $couponClass->getUseCouponData($useCouponParam);
		if ($useCouponData === false) {
			throw new Exception('쿠폰 사용 내역을 가져오면서 오류가 발생했습니다.');
		}
		$couponIdx = $useCouponData->fields['idx'];
		$couponUseBeforeMileage = $useCouponData->fields['coupon_use_before_mileage'];
		$couponUseMileage = $useCouponData->fields['coupon_use_mileage'];
		$discountRate = $useCouponData->fields['discount_rate'];

		$finalPaymentSum = $finalRealPaymentSum = $dealingsData->fields['dealings_mileage']; // 거래잔액

		$dealingsCommission = $dealingsData->fields['dealings_commission']; // 거래수수료
		if ($dealingsCommission < 0) {
			throw new Exception('수수료가 입력되어있지 않습니다.'); 
		}
		$dealingsCommission = ceil(($finalPaymentSum*$dealingsCommission)/100);

		$finalPaymentSum -= $dealingsCommission;
		$discountMoney =  $useCouponData->fields['coupon_use_before_mileage'];

		if (!empty($couponIdx) && $discountRate < 100) {
			$finalRealPaymentSum -= $couponUseMileage;
			$discountMoney =  $useCouponData->fields['coupon_use_mileage'];
		}

		// 거래상태 변경
		$DealingsStatusChangehref = $actionUrl . '?mode=change_status&dealings_idx ='.$getData['type'];

		$dealingsCompleteParam = '?idx=' . $getData['idx'] . '&target=member_idx'; // 파라미터

		$dealingsApproval = DEALINGS_PROCESS_ACCTION . '/seller_dealings_approval.php' . $dealingsCompleteParam; // 거래승인
		$dealingsCancel = DEALINGS_PROCESS_ACCTION . '/seller_dealings_cancel.php' . $dealingsCompleteParam; // 거래취소

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/dealings_purchase_status_view.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃