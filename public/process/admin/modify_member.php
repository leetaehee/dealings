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

        if ($postData['isOverlapEmail'] > 0) {
            // 이메일이 변경 된 경우
            $isNewData = true;
        }

        if ($postData['isOverlapPhone'] > 0) {
            // 핸드폰번호가 변경된 경우
            $isNewData = true;
        }

        // 이메일과 핸드폰이 바뀐 경우에만 중복검사
        if ($isNewData == true) {
            $rAdminP = [
                'phone'=> setEncrypt($postData['phone']),
                'email'=> setEncrypt($postData['email'])
            ];

            $rAdminQ = 'SELECT COUNT(`id`)  cnt 
                        FROM `th_admin`
                        WHERE `phone` = ? OR `email` = ?';

            $rAdminChkResult = $cb->execute($rAdminQ, $rAdminP);
            if ($rAdminChkResult === false) {
                throw new RollbackException('계정 중복 검사를 하면서 오류가 발생했습니다.');
            }

            $accountOverlapCount = $rAdminChkResult->fields['cnt'];
            if ($accountOverlapCount > 0) {
                throw new RollbackException('아이디/이메일/핸드폰 번호는 중복 될 수 없습니다.');
            }
        }

        $uAdminP = [
            'password'=> password_hash($postData['password'], PASSWORD_DEFAULT),
            'email'=> setEncrypt($postData['email']),
            'phone'=> setEncrypt($postData['phone']),
            'name'=> setEncrypt($postData['name']),
            'sex'=> $postData['sex'],
            'birth'=> setEncrypt($postData['birth']),
            'idx'=> $postData['idx']
        ];

        $uAdminQ = 'UPDATE `th_admin` SET 
					 `password` = ?,
					 `email` = ?,
					 `phone` = ?,
					 `name` = ?,
					 `sex` = ?,
					 `birth` = ?,
					 `modify_date` = CURDATE()
					WHERE idx = ?';

        $uAdminResult = $db->execute($uAdminQ, $uAdminP);

        $adminAffectedRow = $db->affected_rows();
        if ($adminAffectedRow < 1) {
            throw new RollbackException('관리자 정보를 수정하면서 오류가 발생했습니다.');
        }

        // 수정 성공 시 마이페이지로 이동
		$returnUrl = SITE_ADMIN_DOMAIN.'/admin_page.php';

        $alertMessage = '관리자 정보가 수정 되었습니다!';

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