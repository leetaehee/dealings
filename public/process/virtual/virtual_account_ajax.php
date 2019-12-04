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

        $memberIdx = $_SESSION['idx'];

		// 트랜잭션 시작
		$db->startTrans();

		// 가상계좌 중복체크
        $rVirtualAccountP = [
            'idx'=> $_SESSION['idx'],
            'account_bank'=> $postData['accountBank']
        ];

        $rVirtualAccountQ = 'SELECT `virtual_account_no`
                             FROM `th_member_virtual_account`
                             WHERE `member_idx` = ?
                             AND `bank_name` = ?
                             FOR UPDATE';

        $rVirtualAccountResult = $db->execute($rVirtualAccountQ, $rVirtualAccountP);
		if ($rVirtualAccountResult === false) {
            throw new RollbackException('가상계좌 조회 오류입니다. 관리자에게 문의하세요.');
        }

		$virtualAccountNo = $rVirtualAccountResult->fields['virtual_account_no'];
		if (empty($virtualAccountNo)) {
		    // 가상계좌 발급

            // 가상계좌번호 임시생성(오늘날짜 시분초 + 회원PK)
            $virtualAccountNo = date('YmdHis'). '' . $memberIdx;

            $cVirtualAccountP = [
                'idx'=> $_SESSION['idx'],
                'account_bank'=> $postData['accountBank'],
                'virtual_account_no'=> setEncrypt($virtualAccountNo)
            ];

            $cVirtualAccountQ = 'INSERT INTO `th_member_virtual_account` SET
                                    `member_idx` = ?,
                                    `bank_name` = ?,
                                    `virtual_account_no` = ?';

            $db->execute($cVirtualAccountQ, $cVirtualAccountP);

            $virtualAccountInsertId = $db->insert_id();
            if ($virtualAccountInsertId < 1) {
                throw new RollbackException('가상 계좌를 발급하면서 오류가 발생했습니다.');
            }

            $result = [
                'isSuccess'=>true,
                'account_no'=>setDecrypt($virtualAccountNo)
            ];
        }

		if (!empty($virtualAccountNo)) {
            $result = [
                'isSuccess'=>true,
                'account_no'=>setDecrypt($virtualAccountNo)
            ];
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