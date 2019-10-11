<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 계좌 설정
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/mailer.lib.php'; // PHP메일보내기

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

    // Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/LoginClass.php';

	// Exception 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
        $alertMessage = ''; // 메세지
        
        if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$returnUrl = SITE_DOMAIN.'/my_account.php';
		
		// injection, xss 방지코드
		$_POST['account_bank'] = htmlspecialchars($_POST['account_bank']);
		$_POST['account_no'] = htmlspecialchars($_POST['account_no']);
		$postData = $_POST;

		$memberClass = new MemberClass($db);

		$resultAccountValidCheck = $memberClass->checkAccountFormValidate($postData);
		if ($resultAccountValidCheck['isValid'] === false) {
			throw new Exception($resultAccountValidCheck['errorMessage']);
        }

		$returnUrl = SITE_DOMAIN.'/mypage.php'; // 마이페이지로 이동

		$param = [
			'accountNo'=>setEncrypt($postData['account_no']),
			'accountBank'=>$postData['account_bank'],
			'idx'=>$_SESSION['idx']
		];

		// 트랜잭션 시작
		$db->startTrans();

		$updateResult = $memberClass->updateMyAccount($param);
		if ($updateResult < 1) {
			throw new RollbackException('계좌정보가 입력이 실패했습니다.');
		}
		
		$alertMessage = '계좌정보가 설정되었습니다!';
		
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