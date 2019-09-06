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

	$returnUrl = ''; // 리턴되는 화면 URL 초기화.
	$mode = isset($_POST['mode']) ?  $_POST['mode'] : $_GET['mode'];

	if ($mode == 'getVirtalAccount') {
		/**
		 * @author: LeeTaeHee
		 * @brief: 가상계좌번호 발급
		 */
		
		$mode = isset($_POST['mode']) ?  $_POST['mode'] : $_GET['mode'];
		$postData = $_POST;
		
		$virtualAccountClass = new VirtualAccountClass($db);
		$resultVirtualAccountValidCheck = $virtualAccountClass->checkFormValidate($postData);

		if ($resultVirtualAccountValidCheck['isValid']==false) {
			$result = ['isSucess'=>false,'errorMessage'=>'은행을 입력하세요.']; // 유효성 검증 
		}

		$param = [
				'idx'=>$postData['idx'],
				'accountBank'=>$postData['accountBank']
			];

		$virtualAccount = $virtualAccountClass->getVirtualAccount($param); // 가상계좌 구하기
		
		if ($resultVirtualAccountValidCheck['isValid']==true) {
			if ($virtualAccount==false) {
				// 가상계좌 발급 
				$isnertResult = $virtualAccountClass->insertVirtualAccount($param);

				if($isnertResult['insert_id'] == false) {
					$result = [
						'isSucess'=>false,
						'errorMessage'=>'오류입니다. 관리자에게 문의하세요.'
					];
				}else{
					$result = [
						'isSucess'=>true,
						'account_no'=>setDecrypt($isnertResult['account_no'])
					];
				}
			}else{
				$result = [
						'isSucess'=>true,
						'account_no'=>setDecrypt($virtualAccount)
					];
			}
		}
		echo json_encode($result);
		exit;
	}