<?php
	/**
	 * 상품권 거래 생성 (판매/구매)
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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/SellItemClass.php';

	// Exception 파일 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화.
        $alertMessage = '';

		if ($connection === false) {
           throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$dealingsClass = new DealingsClass($db);
		$sellItemClass = new SellItemClass($db);

		// injection, xss 방지코드
		$_POST['dealings_state'] = htmlspecialchars($_POST['dealings_state']);
		$_POST['dealings_type'] = htmlspecialchars($_POST['dealings_type']);
		$_POST['dealings_subject'] = htmlspecialchars($_POST['dealings_subject']);
		$_POST['dealings_content'] = htmlspecialchars($_POST['dealings_content']);
		$_POST['item_no'] = htmlspecialchars($_POST['item_no']);
		$_POST['item_money'] = htmlspecialchars($_POST['item_money']);
		$_POST['dealings_mileage'] = htmlspecialchars($_POST['dealings_mileage']);
		$_POST['memo'] = htmlspecialchars($_POST['memo']);

		$itemObjectNo = null;
		if (isset($_POST['item_object_no'])) {
			$_POST['item_object_no'] = htmlspecialchars($_POST['item_object_no']);
			if (isset($_POST['item_object_no'])) {
				$itemObjectNo = setEncrypt($_POST['item_object_no']);
			}
		}
		$postData = $_POST;
		$returnUrl = SITE_DOMAIN . '/voucher_dealings.php';

		// 폼 데이터 받아서 유효성 검증 실패시 리다이렉트 경로
		if ($postData['dealings_state'] !== '거래대기') {
			throw new Exception('유효하지 않은 거래 상태입니다. 다시 시도하세요.');
		}

		$dealingsTypeArray = ['구매', '판매'];
		if (!in_array($postData['dealings_type'], $dealingsTypeArray)) {
			throw new Exception('유효하지 않은 거래 타입입니다. 다시 시도하세요.');
		}

		$resultDealingsValidCheck = $dealingsClass->checkDealingFormValidate($postData);
		if ($resultDealingsValidCheck['isValid'] == false) {
			// 유효성 오류
			throw new Exception($resultDealingsValidCheck['errorMessage']);
		}

		$db->startTrans();

		$expirationDate = date('Y-m-d',strtotime('+5 day',strtotime($today))); // 만료일
		
		// 수수료 데이터 검증 
		$itemIdx = $postData['item_no'];

		$rCommissionQ = 'SELECT `commission` FROM `imi_sell_item` WHERE `idx` = ?';

		$rCommistionResult = $db->execute($rCommissionQ, $itemIdx);
		if ($rCommistionResult === false) {
			throw new RollbackException('수수료 정보를 조회 하면서 오류가 발생했습니다.');
		}

		$commission = $rCommistionResult->fields['commission'];
		if (empty($commission)) {
			throw new RollbackException('수수료 데이터를 조회 할 수 없습니다.');
		}

		// 거래 데이터 
		$cDealingsP = [
			'expiration_date'=> $expirationDate,
			'register_date'=> $today,
			'dealings_type'=> '구매',
			'dealings_subject'=> $postData['dealings_subject'],
			'dealings_content'=> $postData['dealings_content'],
			'item_no'=> $postData['item_no'],
			'item_money'=> $postData['item_money'],
			'item_object_no'=> $itemObjectNo,
			'dealings_mileage'=> $postData['dealings_mileage'],
			'dealings_commission'=> $commission,
			'dealings_status'=> 1,
			'memo'=> $postData['memo'],
			'idx'=> $_SESSION['idx']
		];

		// 거래생성
		$dealingsInsertProcess = $dealingsClass->dealingsInsertProcess($cDealingsP);
		if ($dealingsInsertProcess['result'] === false) {
			throw new RollbackException($dealingsInsertProcess['resultMessage']);
		}
		$dealingsInsertId = $dealingsInsertProcess['insertId'];

		$returnUrl = SITE_DOMAIN.'/voucher_dealings.php';
		$alertMessage = '정상적으로 거래글이 등록되었습니다.';

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