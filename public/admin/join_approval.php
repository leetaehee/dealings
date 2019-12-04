<?php
	/**
	 * 회원가입 메일 승인 화면 
	 */

	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

    // Exception Class
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$title = TITLE_JOIN_APPROVAL . ' | ' . TITLE_ADMIN_SITE_NAME;
		$returnUrl = SITE_ADMIN_DOMAIN;

		$alertMessage = '';

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		if (isset($_GET['idx'])) {
			$memberIdx = htmlspecialchars($_GET['idx']);
		} else {
            $memberIdx = htmlspecialchars($_POST['idx']);
		}

        if (empty($memberIdx)) {
            throw new Exception('잘못된 접근입니다.');
        }

        $returnUrl = SITE_ADMIN_DOMAIN . '/login.php';

        $db->startTrans();

        // 관리자 회원 가입 후 메일 승인이 되어 있는지 조회
        $rJoinApprovalQ = 'SELECT `join_approval_date`
                           FROM `th_admin`
                           WHERE `idx` = ?
                           FOR UPDATE';

        $rJoinApprovalResult = $db->execute($rJoinApprovalQ, $memberIdx);
        if ($rJoinApprovalResult === false) {
            throw new RollbackException('가입 승인여부를 조회하면서 오류가 발생했습니다.');
        }

        // 가입 승인 확인
        $joinApprovalDate = $rJoinApprovalResult->fields['join_approval_date'];
        if ($joinApprovalDate != null) {
            throw new RollbackException('이미 가입 승인 메일을 통해 승인 되어 있습니다.');
        }

        // 가입승인 처리
        $uJoinApprovalQ = 'UPDATE `th_admin` 
                           SET `join_approval_date` = CURDATE() 
                           WHERE `idx` = ?';

        $db->execute($uJoinApprovalQ, $memberIdx);

        $joinApprovalAffectedRow = $db->affected_rows();
        if ($joinApprovalAffectedRow < 0) {
            throw new RollbackException('가입 승인 처리하는 중에 오류가 발생했습니다.');
        }

        $db->completeTrans();

        $templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/join_approval.html.php';
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
        }
    }
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout.html.php'; // 전체 레이아웃