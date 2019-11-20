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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/CouponClass.php';

	// Exception 파일 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화.
        $alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// 글삭제
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

		$db->startTrans();

		// 현재 게시글의 거래타입 가져오기
		$rDealingsQ = 'SELECT `dealings_type` FROM `th_dealings` WHERE `idx` = ?';

		$rDealingsResult = $db->execute($rDealingsQ, $dealingsIdx);
		if ($rDealingsResult === false) {
			throw new RollbackException('거래 타입을 조회 하면서 오류가 발생하였습니다.');
		}

		$dealingsType = $rDealingsResult->fields['dealings_type'];
		if ($dealingsType == 'Y') {
			throw new RollbackException('이미 삭제 된 거래내역입니다.');
		}

		if ($dealingsType == '판매') {
			// 쿠폰 복구
			$couponStatusP = [
				'dealings_idx'=> $dealingsIdx,
				'member_idx'=> $_SESSION['idx'],
				'issue_type'=> '판매',
				'coupon_use_end_date'=> $today,
				'is_refund'=> 'N'
			];

			$couponRefundResult = $couponClass->couponRefundProcess($couponStatusP);
			if ($couponRefundResult['result'] === false) {
				throw new RollbackException($couponRefundResult['resultMessage']);
			}
		}

		$uDeleteDealingsP = [
			'is_del'=> 'Y',
			'dealings_status'=> 6,
			'dealings_idx'=> $dealingsIdx
		];

		$uDealingsQ = 'UPDATE `th_dealings` SET 
						`is_del` = ?,
						`dealings_status` = ?
						WHERE `idx` = ?';

		$uDealingsResult = $db->execute($uDealingsQ, $uDeleteDealingsP);
		$uDealingsAffectedRow = $db->affected_rows();
		if ($uDealingsAffectedRow < 0) {
			throw new RollbackException('거래 글을 삭제하면서 오류가 발생하였습니다.');
		}

		$uDealingsProcP = [
			'dealings_status'=> 6,
			'dealings_idx'=> $dealingsIdx
		];

		$uDealingsProcQ = 'INSERT INTO `th_dealings_process` SET
								`dealings_status_idx` = ?,
								`dealings_idx` = ?,
								`dealings_datetime` = now()';
			
		$uDealingsProcQ = $db->execute($uDealingsProcQ, $uDealingsProcP);
		$uDealingsInsertId = $db->insert_id();

		if ($uDealingsInsertId < 1) {
			throw new RollbackException('거래 절차를 추가 하면서 오류가 발생하였습니다.');
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