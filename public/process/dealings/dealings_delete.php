<?php
	/**
	 * 상품권 거래 삭제 (판매/구매)
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
		$isUseForUpdate = true;

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// 글삭제
		$dealingsClass = new DealingsClass($db);
		$couponClass = new CouponClass($db);

		// return시 url 설정
		$returnUrl = SITE_DOMAIN.'/mypage.php';

		// xss, injection 방지
		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$getData = $_GET;

		if (!isset($getData['idx']) && !empty($getData['idx'])) {
			throw new Exception('정상적인 경로가 아닙니다.');
		}

		$dealingsIdx = $getData['idx'];
		$dealingsStatus = 6;

		$db->startTrans();

		// 거래타입 가져오기
		$dealingsType = $dealingsClass->getDealingsType($dealingsIdx, $isUseForUpdate);
		if ($dealingsType === false) {
			throw new RollbackException('거래타입을 가져오는 중에 오류가 발생했습니다.');
		}

		$couponUseParam = [
			'dealings_idx'=> $dealingsIdx,
			'member_idx'=> $_SESSION['idx'],
			'issue_type'=> $dealingsType == '판매' ? '판매' : '구매',
			'is_refund'=> 'N'
		];

		$useCouponData = $couponClass->getUseCouponData($couponUseParam, $isUseForUpdate);
		if ($useCouponData === false) {
			throw new RollbackException("쿠폰 사용 내역을 가져오면서 오류가 발생했습니다.");
		}

		$couponIdx = $useCouponData->fields['idx'];
		$couponMemberIdx = $useCouponData->fields['coupon_member_idx'];

		if (!empty($couponIdx)){
			// 판매삭제 시 사용내역에 쿠폰 환불입력
			$couponStatusParam = [
				'coupon_use_end_date'=> date('Y-m-d'),
				'is_refund'=> 'Y',
				'idx'=> $couponIdx
			];
		
			$updateCouponResult = $couponClass->updateCouponStatus($couponStatusParam);
			if ($updateCouponResult < 1) {
				throw new RollbackException('판매취소로 쿠폰 복구 중에 문제가 생겼습니다.');
			}
			
			// 판매삭제 시 쿠폰 복구
			$couponStatusName = '사용대기';

			$couponStatusCode = $couponClass->getCouponStatusCode($couponStatusName, $isUseForUpdate);
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

		$deleteParam = [
			'is_del'=> 'Y',
			'dealings_status'=> $dealingsStatus,
			'dealings_idx'=> $dealingsIdx
		];

		$updateResult = $dealingsClass->updateDealingsDeleteStatus($deleteParam);
		if ($updateResult < 1) {
			throw new RollbackException('거래 데이터 삭제 시 오류가 발생했습니다.');
		}

		$alertMessage = '거래 데이터가 정상적으로 삭제 되었습니다.';

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