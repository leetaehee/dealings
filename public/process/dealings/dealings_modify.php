<?php
	/**
	 * 상품권 거래 수정 (판매/구매)
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

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$dealingsClass = new DealingsClass($db);

		// injection, xss 방지코드
		$_POST['dealings_subject'] = htmlspecialchars($_POST['dealings_subject']);
		$_POST['dealings_content'] = htmlspecialchars($_POST['dealings_content']);
		$_POST['dealings_mileage'] = htmlspecialchars($_POST['dealings_mileage']);
		$_POST['memo'] = htmlspecialchars($_POST['memo']);
		$_POST['dealings_idx'] = htmlspecialchars($_POST['dealings_idx']);

		$itemObjectNo = null;
		if (isset($_POST['item_object_no'])) {
			$_POST['item_object_no'] = htmlspecialchars($_POST['item_object_no']);
			if (isset($_POST['item_object_no'])) {
				$itemObjectNo = setEncrypt($_POST['item_object_no']);
			}
		}
		$postData = $_POST;

		// 폼 데이터 받아서 유효성 검증 실패시 리다이렉트 경로
		$returnUrl = SITE_DOMAIN.'/mypage.php';

		$resultDealingsValidCheck = $dealingsClass->checkDealingFormValidate($postData);
		if ($resultDealingsValidCheck['isValid'] == false) {
			// 유효성 오류
			throw new Exception($resultDealingsValidCheck['errorMessage']);
		}

		$db->startTrans();

		$updateData = [
			'dealings_subject'=> $postData['dealings_subject'],
			'dealings_content'=> $postData['dealings_content'],
			'dealings_mileage'=> $postData['dealings_mileage'],
			'memo'=> $postData['memo'],
			'itemObjectNo'=> $itemObjectNo,
			'dealins_idx'=> $postData['dealings_idx']
		];

		$updateResult = $dealingsClass->updateDealings($updateData);
		if ($updateResult < 1) {
			throw new RollbackException('거래데이터 수정 실패하였습니다.');
		}

		$alertMessage = '정상적으로 거래글이 수정되었습니다.';

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