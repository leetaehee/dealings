<?php
	/**
	 * 관리자 탈퇴
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	// Exception 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$alertMessage = '';
		$returnUrl = SITE_ADMIN_DOMAIN;

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		if (isset($_GET['password'])) {
			$password = htmlspecialchars($_GET['password']);
		} else {
			$password = htmlspecialchars($_POST['password']);
		}

		$idx = $_SESSION['mIdx'];

		$isNewData = false;

		// 유효성검증 실패시, 리턴 UTL
		$returnUrl = SITE_ADMIN_DOMAIN.'/admin_delete.php?idx='.$idx;

		$db->startTrans();

        $rCheckPasswordQ = 'SELECT `password` 
                            FROM `th_admin` 
                            where `idx` = ? 
                            FOR UPDATE';

        $rCheckPasswordResult = $db->execute($rCheckPasswordQ, $idx);
        if ($rCheckPasswordResult === false) {
            throw new RollbackException('관리자 패스워드를 조회하면서 오류가 발생했습니다.');
        }

        // 비밀번호 확인하는지 체크
        $dbPassword = $rCheckPasswordResult->fields['password'];
        if (password_verify($password, $dbPassword) == false) {
            throw new RollbackException('비밀번호를 확인하세요!');
        }

        // 관리자 탈퇴
        $uAdminDeleteQ = 'UPDATE `th_admin` 
                           SET `withdraw_date` = CURDATE(),
					          `modify_date` = CURDATE()
				           WHERE `idx` = ?';

        $uAdminDeleteResult = $db->execute($uAdminDeleteQ, $idx);

        $adminDeleteAffectedRow = $db->affected_rows();
        if ($adminDeleteAffectedRow < 1) {
            throw new RollbackException('관리자 탈퇴하면서 오류가 발생하였습니다.');
        }

        $returnUrl = SITE_ADMIN_DOMAIN;

		session_destroy();
		$alertMessage = '정상적으로 탈퇴되었습니다. 이용해주셔서 감사합니다.';
		
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