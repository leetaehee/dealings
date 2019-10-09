<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 상품권 거래 상태 변경 (판매/구매)
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

		if(isset($_POST['coupon_name'])){
			$_POST['coupon_name'] = htmlspecialChars($_POST['coupon_name']);
		}
		$postData = $_POST;

		// returnURL
		$returnUrl = SITE_DOMAIN . '/voucher_dealings.php';

		$dealingsTypeArray = ['구매', '판매'];
		if (!in_array($postData['dealings_type'], $dealingsTypeArray)) {
			throw new Exception('유효하지 않은 거래 타입입니다. 다시 시도하세요.');
		}

		$db->startTrans();

		if (!empty($postData['coupon_name'])) {
			// 해당 쿠폰이 실제 있는 쿠폰인지 체크
			$couponMemberIdx =  $postData['coupon_name']; // 쿠폰 지급 고유키

			$memberCouponData = [
				'coupon_member_idx'=> $couponMemberIdx,
				'is_del'=> 'N'
			];
			// 쿠폰 검증을 위한 쿠폰 고유 키 가져오기
			$couponIdx = $couponClass->getCheckCouponMemberIdx($memberCouponData);
			if ($couponIdx === false) {
				throw new RollbackException('쿠폰의 고객 정보를 가져오면서 오류가 발생했습니다.');
			}
	
			$validParam = [
				'issue_type'=> '판매',
				'idx'=> $couponIdx,
				'is_del'=> 'N'
			];
			
			$isValidCoupon = $couponClass->getCheckValidCoupon($validParam);
			if ($isValidCoupon === false) {
				throw new RollbackException('쿠폰의 키 값을 검사하는 중에 오류가 발생했습니다.');
			}

			if ($isValidCoupon == null) {
				throw new RollbackException('유효하지 않은 쿠폰은 사용 할 수 없습니다.');
			}

			$memberCouponData = [
				'coupon_member_idx'=> $couponMemberIdx,
				'is_del'=> 'N'
			];

			$couponIdx = $couponClass->getCheckCouponMemberIdx($memberCouponData);
			if ($couponIdx === false) {
				throw new RollbackException('쿠폰의 고객 정보를 가져오면서 오류가 발생했습니다.');
			}

			// 쿠폰을 사용했는지 체크 
			$availableCouponParam = [
				'idx'=> $couponIdx,
				'is_del'=> 'N',
				'member_idx'=> $_SESSION['idx'],
				'is_refund'=> 'N'
			];

			$isAvailableCoupon = $couponClass->getCheckAvailableCoupon($availableCouponParam);
			if ($isAvailableCoupon === false) {
				throw new RollbackException('쿠폰 사용 내역을 조회하는 중에 오류가 발생했습니다.');
			}

			if ($isAvailableCoupon != null) {
				throw new RollbackException('해당 쿠폰은 이미 사용했습니다.');
			}
			
			$couponDiscountData = $couponClass->getCouponDiscountData($couponIdx);
			if ($couponDiscountData === false) {
				throw rollbackException('쿠폰 할인 정보 가져오면서 오류가 발생했습니다.');
			}

			$discountRate = $couponDiscountData->fields['discount_rate'];

			$dealingsCommission = $dealingsClass->getCommission($_SESSION['dealings_idx']);
			if ($dealingsCommission === false) {
				throw new RollbackException('거래 수수료를 가져오는 중에 오류가 발생했습니다.');
			}
			
			// 원래 내야 하는 수수료
			$couponUseBeforeMileage = ($_SESSION['dealings_mileage']*$dealingsCommission)/100;
			// 쿠폰을 적용 받아서 내야 하는 수수료 
			$couponUseMileage = ($couponUseBeforeMileage*$discountRate)/100;
		}
		
		$param = [
			'idx'=>$_SESSION['dealings_idx'],
			'is_del'=>'N',
			'dealinges_status'=>$_SESSION['dealings_status'],
		];

		$existCount = $dealingsClass->isDealingsDataExist($param);
		if ($existCount === false){
			throw new RollbackException('거래 데이터 이중등록 체크에 오류가 발생했습니다.');
		}

		if($existCount === 0){
			throw new RollbackException('해당 데이터는 대기상태가 아닙니다!');
		}

		// 다음 거래상태 구하기
		$statusData = [
			'dealings_status'=>$_SESSION['dealings_status']
		];

		$nextStatus = $dealingsClass->getNextDealingsStatus($statusData);
		if ($nextStatus === false) {
			throw new RollbackException('거래 상태에 오류가 발생했습니다.');
		}

		$dealingsExistCount = $dealingsClass->isExistDealingsIdx($_SESSION['dealings_idx']);
		if ($dealingsExistCount === false) {
			throw new RollbackException('거래 유저 테이블에 오류가 발생했습니다.');
		}

		if ($_SESSION['dealings_status'] == 1 && $dealingsExistCount == 0) {
			// 판매대기, 구매대기의 경우 거래유저테이블에 데이터 생성	
			$userData = [
				'dealings_idx'=>$_SESSION['dealings_idx'],
				'dealings_writer_idx'=>$_SESSION['dealings_writer_idx'],
				'dealings_member_idx'=>$_SESSION['idx'],
				'dealings_status'=>$nextStatus,
				'dealings_type'=>$postData['dealings_type']
			];

			$insertResult = $dealingsClass->insertDealingsUser($userData);
			if ($insertResult < 1) {
				throw new RollbackException('거래 유저 생성 실패하였습니다.');
			}
		} else {
			// 거래유저테이블에 데이터 수정
			$userData = [
				'dealings_status'=>$nextStatus,
				'dealings_idx'=>$_SESSION['dealings_idx']
			];

			$updateResult = $dealingsClass->updateDealingsUser($userData);
			if ($updateResult < 1) {
				throw new RollbackException('거래 유저 수정 실패하였습니다.');
			}
		}

		$dealingsParam = [
			'nextStatus'=>$nextStatus,
			'idx'=>$_SESSION['dealings_idx']
		];

		// 거래테이블 상태변경 
		$updateDealingsStatus = $dealingsClass->updateDealingsStatus($dealingsParam);
		if ($updateDealingsStatus < 1) {
			throw new RollbackException('거래 상태 정보 수정 실패하였습니다.');
		}

		// 처리절차 생성하기
		$processData = [
			'dealings_idx'=>$_SESSION['dealings_idx'],
			'dealings_status_idx'=>$nextStatus
		];

		$insertProcessResult = $dealingsClass->insertDealingsProcess($processData);
		if ($insertProcessResult < 1) {
			throw new RollbackException('거래 처리과정 생성 실패하였습니다.');
		}

		// 구매 시 쿠폰 적용했다면 사용내역에 데이터 생성.
		if (!empty($postData['coupon_name'])) {
			$useageData = [
				'type'=> '판매',
				'dealings_idx'=> $_SESSION['dealings_idx'],
				'coupon_idx'=> $couponIdx,
				'member_idx'=> $_SESSION['idx'],
				'coupon_use_before_mileage'=> $couponUseBeforeMileage,
				'coupon_use_mileage'=> $couponUseMileage,
				'coupon_member_idx'=> $couponMemberIdx,
			];

			$insertUseageDataResult = $couponClass->insertCouponUseage($useageData);
			if ($insertUseageDataResult < 1) {
				throw new RollbackException('쿠폰 사용 내역을 입력하는 중에 오류가 발생했습니다.');
			}

			$couponStatusName = '사용완료';

			$couponStatusCode = $couponClass->getCouponStatusCode($couponStatusName);
			if ($couponStatusCode === false) {
				throw new RollbackException('쿠폰 상태 코드를 가져오면서 오류가 발생했습니다.');
			}

			if (empty($couponStatusCode)) {
				throw new RollbackException('쿠폰 상태 코드를 찾을 수 없습니다.');
			}

			$couponMbStParam = [
				'coupon_status'=> $couponStatusCode,
				'idx'=> $couponMemberIdx
			];

			$updateCouponMbStatusResult = $couponClass->updateCouponMemberStatus($couponMbStParam);
			if ($updateCouponMbStatusResult < 1) {
				throw new RollbackException('쿠폰 상태 코드를 변경하면서 오류가 발생했습니다.');
			}
		}

		$_SESSION['dealings_writer_idx'] = '';
		$_SESSION['dealings_idx'] = '';
		$_SESSION['dealigng_status'] = '';
		$_SESSION['dealingsMileage'] = '';

		$returnUrl = SITE_DOMAIN.'/voucher_dealings.php';
		$alertMessage = '정상적으로 거래 상태가 변경되었습니다.';

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