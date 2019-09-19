<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: ajax 통신 
	 */

	header("Content-Type: application/json"); 

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/mailer.lib.php'; // PHP메일보내기

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php'; // adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php'; // adodb

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/VirtualAccountClass.php'; // Class 파일

	try {
		if (isset($_POST['mode'])) {
			$mode = htmlspecialchars($_POST['mode']);
		} else {
			$mode = htmlspecialchars($_POST['GET']);
		}

		if ($mode == 'getVirtalAccount') {
			/**
			 * @author: LeeTaeHee
			 * @brief: 가상계좌번호 발급
			 */

			// xss, inject 방지코드
			$_POST['accountBank'] = htmlspecialchars($_POST['accountBank']);
			$postData = $_POST;
			
			$virtualAccountClass = new VirtualAccountClass($db);
			$resultVirtualAccountValidCheck = $virtualAccountClass->checkFormValidate($postData);

			if ($resultVirtualAccountValidCheck['isValid'] === false) {
				throw new Exception('은행을 입력하세요.');
			} else {
				$param = [
					'idx'=>$_SESSION['idx'],
					'accountBank'=>$postData['accountBank']
				];
				$virtualAccount = $virtualAccountClass->getVirtualAccount($param); // 가상계좌 구하기
				if ($virtualAccount === false) {
					throw new Exception('가상계좌 조회 오류입니다. 관리자에게 문의하세요.');
				} else {
					// 가상계좌 발급 
					if ($virtualAccount===null) {
						$insertResult = $virtualAccountClass->insertVirtualAccount($param);
						if ($insertResult['insert_id'] == false) {
							throw new Exception('가상계좌 생성 오류입니다. 관리자에게 문의하세요');
						} else {
							$result = [
								'isSuccess'=>true,
								'account_no'=>setDecrypt($insertResult['account_no'])
							];
						}
					} else {
						$result = [
							'isSuccess'=>true,
							'account_no'=>setDecrypt($virtualAccount)
						];
					}
				}
			}
		}
	} catch(Exception $e) {
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