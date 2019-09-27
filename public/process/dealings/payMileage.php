<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 상품권 거래 결제 (판매/구매)
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
		$mileageClass = new MileageClass($db);
		$memberClass = new MemberClass($db);
		$couponClass = new CouponClass($db);

		$_POST['dealings_type'] = htmlspecialchars($_POST['dealings_type']);
		$_POST['dealings_mileage'] = htmlspecialchars($_POST['dealings_mileage']);
		 
		if (isset($_POST['coupon_name'])) {
			$_POST['coupon_name'] = htmlspecialChars($_POST['coupon_name']);
		}
		$postData = $_POST;

		$returnUrl = SITE_DOMAIN.'/voucher_dealings.php';

		$db->startTrans();

		if (!in_array($postData['dealings_type'], $CONFIG_COUPON_ISSUE_TYPE)) {
			throw new Exception('유효하지 않은 거래 타입입니다. 다시 시도하세요.');
		}

		// 쿠폰을 사용한 경우 유효성체크
		if (!empty($postData['coupon_name'])) {
			// 해당 쿠폰이 실제 있는 쿠폰인지 체크
			$couponIdx = $postData['coupon_name'];

			$validParam = [
				'issue_type'=> '구매',
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

			$discountMileage = $couponClass->getDiscountMileage($couponIdx);
			if ($discountMileage === false) {
				throw new RollbackException('쿠폰 할인 금액을 조회하는 중에 오류가 발생했습니다.');
			} 
			
			//imi_mileage_charge 수량 변경을 위한 정보 얻어오기
			if ($discountMileage > 0) {
				$dealingsMileage = $postData['dealings_mileage'] - $discountMileage; 

				if ($dealingsMileage < 0) {
					$dealingsMileage = 0;
				}
			}
		} else {
			$dealingsMileage = $postData['dealings_mileage']; 
		}

		// 거래정보 추출(폼데이터 검증)
		$validDealingsData = $dealingsClass->getValidDealingsData($_SESSION['dealings_idx']);
		if ($validDealingsData === false) {
			throw new RollbackException('유효한 거래정보 가져오는 중에 오류가 발생했습니다.');
		}

		$validDealingsMileage = $validDealingsData->fields['dealings_mileage'];
		if ($validDealingsMileage != $postData['dealings_mileage']){
			throw new RollbackException('거래 금액이 상이합니다. 다시 진행하세요!');
		}

		// 거래금액 확인
		$totalMileage = $memberClass->getTotalMileage($_SESSION['idx']);
		if ($totalMileage === false) {
			throw new RollbackException('거래 상태를 가져올 수 없습니다.');
		}

		if ($dealingsMileage > $totalMileage) {
			throw new RollbackException('거래금액이 부족합니다! 충전하세요');
		}

		if ($dealingsMileage < 0) {
			throw new RollbackException('거래금액이 0 보다 작을 수 없습니다.');
		}

		$param = [
			'member_idx'=>$_SESSION['idx'],
			'charge_status'=>3
		];

		// 거래가능한 마일리지 리스트 가져오기
		$mileageWitdrawalList = $mileageClass->getMileageWithdrawalPossibleList($param);
		if ($mileageWitdrawalList === false) {
			throw new RollbackException('마일리지 충전 내역을 가져오는 중에 오류가 발생하였습니다.');
		}

		// 다음 거래상태 구하기
		$statusData = [
			'dealings_status'=>$_SESSION['dealings_status']
		];

		$nextStatus = $dealingsClass->getNextDealingsStatus($statusData);
		if ($nextStatus === false) {
			throw new RollbackException('마일리지 거래상태 가져오는 중에 오류가 발생했습니다.');
		}

		// 쿠폰을 써서 결제금액이 0원일 때는 차감하지 않는다.
		if ($dealingsMileage > 0) {
			$chargeArray = $mileageClass->getMildateChargeInfomationData($mileageWitdrawalList, $dealingsMileage);
			if ($chargeArray === false) {
				throw new RollbackException('마일리지 수량정보 데이터를 가져오는 중에 오류가 발생하였습니다.');
			}
			$chargeData = $chargeArray['update_data'];
			$typeChargeData = $chargeArray['mileage_type_data'];

			$updateChargeResult = $mileageClass->updateMileageCharge($chargeData);
			if ($updateChargeResult === false) {
				throw new RollbackException('마일리지 출금 시 수량 정보 수정 실패하였습니다.');
			}

			$spareZeroCount = $mileageClass->getCountChargeSpareCountZero();
			if ($spareZeroCount < 0) {
				throw new RollbackException('마일리지 사용금액이 0인 항목은 존재하지 않습니다.');
			}

			if ($spareZeroCount > 0) {
				$updateZeroResult = $mileageClass->updateChargeZeroStatus();
				if ($updateZeroResult === false) {
					throw new RollbackException('마일리지 출금 상태 변경 중에 실패하였습니다.');
				}
			}

			$mileageChangeParam = [
				'dealings_idx'=>$_SESSION['dealings_idx'],
				'dealings_writer_idx'=>$_SESSION['dealings_writer_idx'],
				'dealings_member_idx'=>$_SESSION['purchaser_idx'],
				'dealings_status_code'=>$nextStatus
			];

			$changeData = $mileageClass->updateDealingsMileageArray($chargeData, $mileageChangeParam);
			if ($changeData === false) {
				throw new RollbackException('두 개의 배열을 합치는데 오류가 발생했습니다.');
			}

			// 출금데이터 생성
			$insertChangeResult = $mileageClass->insertDealingsMileageChange($changeData);
			if ($insertChangeResult < 1) {
				throw new RollbackException('거래 출금데이터 생성 실패 하였습니다!');
			}

			$mileageParam = [
				'mileage'=>$dealingsMileage,
				'idx'=>$_SESSION['purchaser_idx']
			];

			$updateResult = $memberClass->updateMileageWithdrawal($mileageParam); // 마일리지변경
			if ($updateResult < 1) {
				throw new RollbackException('회원 마일리지 수정 실패하였습니다.');
			}

			$pusrchaserIdx = $_SESSION['purchaser_idx'];
			$mileageTypeUpdate = $mileageClass->mileageAllTypeWithdrawalUpdate($pusrchaserIdx, $typeChargeData);
			if ($mileageTypeUpdate < 1) {
				throw new RollbackException('마일리지 유형별 출금금액 수정 실패하였습니다.');
			}
		}

		// 거래 데이터 상태 수정
		$dealingsParam = [
			'dealings_status'=>$nextStatus,
			'idx'=>$_SESSION['dealings_idx']
		];

		$updateDealingsStatusResult = $dealingsClass->updateDealingsStatus($dealingsParam);
		if ($updateDealingsStatusResult < 1) {
			throw new RollbackException('거래 데이터 상태 변동중에 오류가 발생했습니다.');
		}

		// 거래유저정보 상태 수정
		$userData = [
			'dealings_status'=>$nextStatus,
			'dealings_idx'=>$_SESSION['dealings_idx']
		];

		$updateDealingsUserResult = $dealingsClass->updateDealingsUser($userData);
		if ($updateDealingsUserResult < 1) {
			throw new RollbackException('거래 유저 수정 실패하였습니다.');
		}

		// 처리절차 생성하기
		$processData = [
			'dealings_idx'=>$_SESSION['dealings_idx'],
			'dealings_status_idx'=>$nextStatus
		];

		$insertProcessResult = $dealingsClass->insertDealingsProcess($processData);
		if ($insertProcessResult < 1) {
			throw new RollbackException('거래 처리과정 생성 실패 하였습니다.');
		}

		// 구매 시 쿠폰 적용했다면 사용내역에 데이터 생성.
		if (!empty($postData['coupon_name'])) {
			$useageData = [
				'type'=> '구매',
				'dealings_idx'=> $_SESSION['dealings_idx'],
				'coupon_idx'=> $postData['coupon_name'],
				'member_idx'=> $_SESSION['idx'],
				'coupon_use_before_mileage'=> $postData['dealings_mileage'],
				'coupon_use_mileage'=> $dealingsMileage
			];

			$insertUseageDataResult = $couponClass->insertCouponUseage($useageData);
			if ($insertUseageDataResult < 1) {
				throw new RollbackException('쿠폰 사용 내역을 입력하는 중에 오류가 발생했습니다.');
			}
		}

		$_SESSION['dealings_writer_idx'] = '';
		$_SESSION['dealings_idx'] = '';
		$_SESSION['dealigng_status'] = '';
		$_SESSION['purchaser_idx'] = '';
		$_SESSION['purchaser_mileage'] = '';
		
		$alertMessage = '결제가 완료되었습니다.';

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