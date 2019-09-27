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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/DealingsClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MileageClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/DealingsCommissionClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/SellItemClass.php';
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
		$returnUrl = SITE_DOMAIN . '/voucher_dealings.php';

		$dealingsTypeArray = ['구매', '판매'];
		if (!in_array($postData['dealings_type'], $dealingsTypeArray)) {
			throw new Exception('유효하지 않은 거래 타입입니다. 다시 시도하세요.');
		}

		$db->startTrans();

		// 거래 데이터 상태 수정
		$dealingsParam = [
			'dealings_status'=>1,
			'idx'=>$_SESSION['dealings_idx']
		];

		$updateDealingsStatusResult = $dealingsClass->updateDealingsStatus($dealingsParam);
		if ($updateDealingsStatusResult < 1) {
			throw new RollbackException('거래 데이터 상태 수정 실패하였습니다.');
		}

		// 거래유저정보 상태 수정
		$userData = [
			'dealings_status'=>1,
			'dealings_idx'=>$_SESSION['dealings_idx']
		];

		$updateDealingsUserResult = $dealingsClass->updateDealingsUser($userData);
		if ($updateDealingsUserResult < 1) {
			throw new RollbackException('거래 유저 상태 수정 실패하였습니다.');
		}

		// 처리절차 생성하기
		$processData = [
			'dealings_idx'=>$_SESSION['dealings_idx'],
			'dealings_status_idx'=>1
		];

		$insertProcessResult = $dealingsClass->insertDealingsProcess($processData);
		if ($insertProcessResult < 1) {
			throw new RollbackException('거래 처리과정 생성 실패하였습니다.');
		}

		$couponUseParam = [
			'dealings_idx'=>$_SESSION['dealings_idx'],
			'member_idx'=>$_SESSION['idx'],
			'issue_type'=>'판매',
			'is_refund'=>'N'
		];

		$useCouponData = $couponClass->getUseCouponData($couponUseParam);
		if ($useCouponData === false) {
			throw new RollbackException("쿠폰 사용 내역을 가져오면 오류가 발생했습니다.");
		}
		$couponIdx = $useCouponData->fields['idx'];

		if (!empty($couponIdx)){
			// 판매취소시 쿠폰복구 
			$couponStatusParam = [
					'coupon_use_end_date'=> date('Y-m-d'),
					'is_refund'=> 'Y',
					'idx'=>$couponIdx
			];

			$updateCouponResult = $couponClass->updateCouponStatus($couponStatusParam);
			if ($updateCouponResult < 1) {
				throw new RollbackException('판매취소로 쿠폰 복구 중에 문제가 생겼습니다.');
			}
		}

		$returnUrl = SITE_DOMAIN.'/voucher_dealings.php';
		$alertMessage = '판매가 취소되었습니다!';

		$db->commitTrans();
		$db->completeTrans();
	} catch (RollbackException $e) {
		// 트랜잭션 문제가 발생했을 때
		$alertMessage = $e->errorMessage();
		$db->rollbackTrans();
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