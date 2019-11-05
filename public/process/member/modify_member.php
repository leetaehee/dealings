<?php
	/**
	 * 회원정보 수정
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
		$isUseForUpdate = true;

        if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$memberClass = new MemberClass($db);
		$isNewData = false;

		// injection, xss 방지코드
		$_POST['isOverlapEmail'] = htmlspecialchars($_POST['isOverlapEmail']);
		$_POST['isOverlapPhone'] = htmlspecialchars($_POST['isOverlapPhone']);
		$_POST['password'] = htmlspecialchars($_POST['password']);
		$_POST['repassword'] = htmlspecialchars($_POST['repassword']);
		$_POST['name'] = htmlspecialchars($_POST['name']);
		$_POST['email'] = htmlspecialchars($_POST['email']);
		$_POST['phone'] = htmlspecialchars($_POST['phone']);
		$_POST['birth'] = htmlspecialchars($_POST['birth']);
		$_POST['sex'] = htmlspecialchars($_POST['sex']);
		$postData = $_POST; // 폼데이터

		$idx = $_SESSION['idx'];
		$returnUrl = SITE_DOMAIN.'/member_modify.php?idx='.$idx; // 유효성검증 실패시, 리턴 UTL

		$resultMemberValidCheck = $memberClass->checkMemberFormValidate($postData);
		if ($resultMemberValidCheck['isValid'] == false) {
			throw new Exception($resultMemberValidCheck['errorMessage']);
		}

		$db->startTrans();
		
		if ($postData['isOverlapEmail'] > 0) {
			$isNewData = true; // 이메일 주소를 변경하는 경우
		}
		
		if ($postData['isOverlapPhone'] > 0) {
			$isNewData = true;  // 핸드폰번호를 변경하는 경우
		}

		if ($isNewData == true) {
            // 기존에 등록된 계정정보는 중복검사 하지말 것.
            $rMemberP = [
                'phone' => setEncrypt($postData['phone']),
                'email' => setEncrypt($postData['email'])
            ];

            $rMemberQ = 'SELECT COUNT(`id`)  cnt 
                         FROM `th_members`
                         WHERE `phone` = ? OR `email` = ?';

            $rMemberChkResult = $db->execute($rMemberQ, $rMemberP);
            if ($rMemberChkResult === false) {
                throw new RollbackException('계정 중복 검사를 하면서 오류가 발생했습니다.');
            }

            $accountOverlapCount = $rMemberChkResult->fields['cnt'];
            if ($accountOverlapCount > 0) {
                throw new RollbackException('아이디/이메일/핸드폰 번호는 중복 될 수 없습니다.');
            }
        }

        $uMemberP = [
            'password'=> password_hash($postData['password'], PASSWORD_DEFAULT),
            'email'=> setEncrypt($postData['email']),
            'phone'=> setEncrypt($postData['phone']),
            'name'=> setEncrypt($postData['name']),
            'sex'=> $postData['sex'],
            'birth'=> setEncrypt($postData['birth']),
            'member_idx'=> $idx
        ];

        $uMemberQ = 'UPDATE `th_members` SET
					  `password` = ?,
					   `email` = ?,
					   `phone` = ?,
					   `name` = ?,
					   `sex` = ?,
					   `birth` = ?,
					   `modify_date` = CURDATE()
		 			 WHERE idx = ?';

        $uMemberResult = $db->execute($uMemberQ, $uMemberP);

        $memberAffectedRow = $db->affected_rows();
        if ($memberAffectedRow < 1) {
            throw new RollbackException('회원정보를 수정하면서 오류가 발생했습니다.');
        }

		// 수정 성공 시 마이페이지로 이동
		$returnUrl = SITE_DOMAIN.'/mypage.php';
		
		$alertMessage = '회원정보가 수정 되었습니다.'; 

		$db->completeTrans();
	} catch (RollbackException $e) {
		$alertMessage = $e->getMessage();
		
		$db->failTrans();
		$db->completeTrans();
	} catch (Exception $e) {
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