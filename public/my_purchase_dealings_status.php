<?php
	/**
	 * 나의 구매 등록현황 상세 화면 
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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/CouponClass.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_VOUCHER_PURCHASE_ENROLL_STATUS . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN.'/mypage.php'; // 리턴되는 화면 URL 초기화
		$alertMessage = '';

		$actionUrl = DEALINGS_PROCESS_ACCTION . '/payMileage.php';
		$JsTemplateUrl = JS_URL . '/my_purchase_dealings_status.js';
		$dealingsType = '판매';
		$btnName = '결제하기';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// xss, injection 방지
		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$_GET['type'] = htmlspecialchars($_GET['type']);
		$getData = $_GET;

		$memberClass = new MemberClass($db);
		$dealingsClass = new DealingsClass($db);
		$couponClass = new CouponClass($db);

		$dealingsData = $dealingsClass->getDealingsData($getData['idx']);
		if ($dealingsData === false) {
			throw new Exception('회원 구매 거래정보를 가져 올 수 없습니다.');
		}

		// 구매자 정보 갖고오기
		$dealingsMemberIdx = $dealingsData->fields['dealings_member_idx'];
		$itemMoney = $dealingsData->fields['item_money'];
        $itemNo = $dealingsData->fields['item_no'];

		$_SESSION['dealings_writer_idx'] = $dealingsData->fields['writer_idx'];
		$_SESSION['dealings_idx'] = $getData['idx'];
		$_SESSION['dealings_status'] = $getData['type'];

		$purchaserData = $memberClass->getMyInfomation($dealingsMemberIdx);
		if ($purchaserData === false) {
			throw new Exception('구매자 정보를 가져 올 수 없습니다.');
		} else {
			$purchaserDataCount = $purchaserData->recordCount();
		}

		$_SESSION['purchaser_idx'] = $dealingsData->fields['writer_idx'];
		$_SESSION['purchaser_mileage'] = $dealingsData->fields['dealings_mileage'];

		// 이용가능한 마일리지 조회 
		$memberIdx = $_SESSION['idx'];
		$totalMileage = $memberClass->getTotalMileage($memberIdx);

		// 이용가능한 쿠폰 가져오기
		$couponParam = [
			'sell_item_idx'=> $itemNo,
			'issue_type'=> '구매',
			'item_money'=> $itemMoney,
			'is_coupon_del'=> 'N',
			'is_del'=> 'N',
			'member_idx'=> $_SESSION['idx'],
			'coupon_status'=> 1
		];
		
		// 사용가능한 쿠폰 리스트 가져오기
		$couponData = $couponClass->getAvailableCouponData($couponParam);
		if ($couponData === false) {
			throw new Exception('사용가능한 쿠폰을 가져 올 수 없습니다. 가져 올 수 없습니다');
		} else {
			$couponDataCount = $couponData->recordCount();
		}

		$dealingsMileage = $dealingsData->fields['dealings_mileage'];
		$finalPaymentSum = $dealingsData->fields['dealings_mileage']; // 거래잔액
		$dealingsCommission = $dealingsData->fields['dealings_commission']; // 거래수수료

		if ($dealingsCommission < 0) {
			throw new Exception('수수료가 입력되어있지 않습니다.'); 
		}

		$dealingsCommission = ceil(($finalPaymentSum*$dealingsCommission)/100);
		$finalPaymentSum -= $dealingsCommission;

		// 사용한 쿠폰정보 가져오기
		$useCouponParam = [
			'dealings_idx'=> $getData['idx'],
			'member_idx'=> $_SESSION['idx'],
			'issue_type'=> '구매',
			'is_refund'=> 'N'
		];

		$useCouponData = $couponClass->getUseCouponData($useCouponParam);
		if ($useCouponData === false) {
			throw new Exception('쿠폰 사용 내역을 가져오면서 오류가 발생했습니다.');
		}
		$couponIdx = $useCouponData->fields['idx'];

		// 거래상태 변경
		$DealingsStatusChangehref = $actionUrl . '?mode=change_status&dealings_idx ='.$getData['type'];

		$dealingsModifyUrl = SITE_DOMAIN . '/purchase_dealings_modify.php?idx=' . $getData['idx']; // 구매거래수정 
		$dealingsDeleteUrl = DEALINGS_PROCESS_ACCTION . '/dealings_delete.php?idx=' . $getData['idx']; // 거래삭제

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/my_purchase_dealings_status.html.php';
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