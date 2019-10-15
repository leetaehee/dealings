<?php
	/**
	 * 관리자 정보 수정
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/mailer.lib.php';

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

    // Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/AdminClass.php';

	// Exception 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$isNewData = false;

		$alertMessage = '';
		$isUseForUpdate = true;

		$returnUrl = SITE_ADMIN_DOMAIN;

		$adminClass = new AdminClass($db);

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// injection, xss 방지코드
		$_POST['isOverlapEmail'] = htmlspecialchars($_POST['isOverlapEmail']);
		$_POST['isOverlapPhone'] = htmlspecialchars($_POST['isOverlapPhone']);
		$_POST['idx'] = htmlspecialchars($_POST['idx']);
		$_POST['password'] = htmlspecialchars($_POST['password']);
		$_POST['repassword'] = htmlspecialchars($_POST['repassword']);
		$_POST['name'] = htmlspecialchars($_POST['name']);
		$_POST['email'] = htmlspecialchars($_POST['email']);
		$_POST['phone'] = htmlspecialchars($_POST['phone']);
		$_POST['birth'] = htmlspecialchars($_POST['birth']);
		$_POST['sex'] = htmlspecialchars($_POST['sex']);
		$postData = $_POST;

		// 유효성검증 실패시, 리턴 UTL
		$returnUrl = SITE_ADMIN_DOMAIN.'/admin_modify.php?idx='.$postData['idx'];

		$resultAdminValidCheck = $adminClass->checkAdminFormValidate($postData);
		if ($resultAdminValidCheck['isValid'] == false) {
			throw new Exception($resultAdminValidCheck['errorMessage']);
		}
		
		$db->startTrans();

		$accountData = [
			'phone'=> setEncrypt($postData['phone']),
			'email'=> setEncrypt($postData['email'])
		];
			
		if ($postData['isOverlapEmail'] > 0) {
			$isNewData = true; // 이메일 주소를 변경하는 경우
		}
		
		if ($postData['isOverlapPhone'] > 0) {
			$isNewData = true;  // 핸드폰번호를 변경하는 경우
		}
		if ($isNewData === true) {
			$accountOverlapCount = $adminClass->getAccountOverlapCount($accountData, $isUseForUpdate);
			
			if ($accountOverlapCount === false) {
				throw new RollbackException('계정중복확인 오류 발생! 관리자에게 문의하세요');
			} 
			
			if ($accountOverlapCount > 0) {
				throw new RollbackException('이메일/핸드폰 번호는 중복 될 수 없습니다.');
			}
		}

		$updateResult = $adminClass->updateAdmin($postData);
		if ($updateResult < 1) {
			throw new RollbackException('회원정보 수정이 실패하였습니다! 관리자에게 문의하세요.');
		}
		
		$alertMessage = '회원정보가 수정 되었습니다!';

		$returnUrl = SITE_ADMIN_DOMAIN.'/admin_page.php'; // 수정 성공 시 마이페이지로 이동
		
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
            alertMsg(SITE_ADMIN_DOMAIN, 0);
        }
    }