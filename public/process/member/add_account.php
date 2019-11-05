<?php
	/**
	 * 계좌 설정
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

    // Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php';

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


		// 트랜잭션 시작
		$db->startTrans();

        $returnUrl = SITE_DOMAIN.'/mypage.php'; // 마이페이지로 이동

        $uMyAccountP = [
            'accountNo'=> setEncrypt($postData['account_no']),
            'accountBank'=> $postData['account_bank'],
            'idx'=> $_SESSION['idx']
        ];

        $uMyAccountQ = 'UPDATE `th_members` SET
					      `account_no` = ?,
					      `account_bank` = ?
					    WHERE `idx` = ?';

        $uMyAccountResult = $db->execute($uMyAccountQ, $uMyAccountP);

        $myAccountAffectedRow = $db->affected_rows();
        if ($myAccountAffectedRow < 1) {
            throw new RollbackException('계좌를 설정하면서 오류가 발생하였습니다.');
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