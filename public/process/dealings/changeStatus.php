<?php
	/**
	 * 거래 변경 시 상태변경 
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

	// Exception 파일 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화.
        $alertMessage = '';
		$isUseForUpdate = true;

		if ($connection === false) {
           throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$dealingsClass = new DealingsClass($db);

		$_POST['dealings_type'] = htmlspecialchars($_POST['dealings_type']);
		$postData = $_POST;

		$memberIdx = $_SESSION['idx'];

		// returnURL
		$returnUrl = SITE_DOMAIN . '/voucher_dealings.php';

		$dealingsTypeArray = ['구매', '판매'];
		if (!in_array($postData['dealings_type'], $dealingsTypeArray)) {
			throw new Exception('유효하지 않은 거래 타입입니다. 다시 시도하세요.');
		}

		if (!array_key_exists($_SESSION['dealings_status'], $CONFIG_DEALINGS_STATUS_CODE)){
			throw new Exception('거래 상태가 유효하지 않습니다.');
		}

		$db->startTrans();

		$rDealingsExistP = [
			'idx'=> $_SESSION['dealings_idx'],
			'is_del'=> 'N',
			'dealinges_status'=> $_SESSION['dealings_status'],
		];

		$rDealingsExistQ = 'SELECT count(`idx`) cnt 
							FROM `imi_dealings`
							WHERE `idx` = ? 
							AND `is_del` = ?
							AND `dealings_status` = ?';
			
		$rDealingsExistResult = $db->execute($rDealingsExistQ, $rDealingsExistP);
		if ($rDealingsExistResult === false) {
			throw new RollbackException('거래 이중 등록 확인하는 중에 오류가 발생했습니다.');
		}

		$existCount = $rDealingsExistResult->fields['cnt'];
		if ($existCount == 0) {
			throw new RollbackException('해당 거래글은 거래대기 상태가 아닙니다.');
		}

		// 거래 상태 파라미터
		$dealingsStPcParam = [
			'dealings_status'=> $_SESSION['dealings_status'],
			'dealings_idx'=> $_SESSION['dealings_idx']
		];

		// 거래상태 관련
		$dealingsProcessResult = $dealingsClass->dealignsStatusProcess($dealingsStPcParam, $memberIdx);
		if ($dealingsProcessResult['result'] === false) {
			throw new RollbackException($dealingsProcessResult['resultMessage']);
		}

		$_SESSION['dealings_writer_idx'] = '';
		$_SESSION['dealings_idx'] = '';
		$_SESSION['dealigng_status'] = '';

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