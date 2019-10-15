<?php
	/**
	 * ajax 통신(가상계좌 조회 및 발급)
	 */

	header("Content-Type: application/json"); 

	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	// Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/VirtualAccountClass.php';

	// Exception 파일 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$isUseForUpdate = true;

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// xss, inject 방지코드
		$_POST['accountBank'] = htmlspecialchars($_POST['accountBank']);
		$postData = $_POST;
		
		$virtualAccountClass = new VirtualAccountClass($db);
		$resultVirtualAccountValidCheck = $virtualAccountClass->checkFormValidate($postData);

		if ($resultVirtualAccountValidCheck['isValid'] === false) {
			throw new Exception('은행을 입력하세요.');
		}

		if (!in_array($postData['accountBank'], $CONFIG_BANK_ARRAY)) {
			throw new Exception('은행에 잘못된 값이 들어왔습니다. 다시 선택하세요.');
		}

		// 트랜잭션 시작
		$db->startTrans();

		$param = [
			'idx'=>$_SESSION['idx'],
			'accountBank'=>$postData['accountBank']
		];

		$virtualAccount = $virtualAccountClass->getVirtualAccount($param, $isUseForUpdate); // 가상계좌 구하기
		if ($virtualAccount === false) {
			throw new RollbackException('가상계좌 조회 오류입니다. 관리자에게 문의하세요.');
		} else {
			// 가상계좌 발급 
			if ($virtualAccount === null) {
				$insertResult = $virtualAccountClass->insertVirtualAccount($param);

				if ($insertResult === false) {
					throw new RollbackException('가상계좌 생성 오류입니다. 관리자에게 문의하세요');
				}

				$result = [
					'isSuccess'=>true,
					'account_no'=>setDecrypt($insertResult['account_no'])
				];
			} else {
				$result = [
					'isSuccess'=>true,
					'account_no'=>setDecrypt($virtualAccount)
				];
			}
		}
		$db->completeTrans();
	} catch (RollbackException $e) {
		// 트랜잭션 문제가 발생했을 때
		$result = [
			'isSuccess'=>false, 
			'errorMessage'=> $e->getMessage()
		];

		$db->failTrans();
		$db->completeTrans();
	} catch (Exception $e) {
		// 트랜잭션을 사용하지 않을 때
		$result = [
			'isSuccess'=>false, 
			'errorMessage'=> $e->getMessage()
		];
    } finally {
        if  ($connection === true) {
            $db->close();
        }
		
		echo json_encode($result);
		exit;
    }