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

	// Exception 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_JOIN_APPROVAL . ' | ' . TITLE_SITE_NAME;
		$alertMessage = '';

		$returnUrl = SITE_DOMAIN;

		if ($connection === false) {
            throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		// xss, injection 방지코드
		if (isset($_GET['idx'])) {
			$memberIdx = htmlspecialchars($_GET['idx']);
		} else {
			$memberIdx = htmlspecialchars($_POST['idx']);
		}

		if (empty($memberIdx)) {
            throw new Exception('잘못된 접근입니다!');
        }

        $db->startTrans();

		// 가입승인여부 확인
        $rJoinApprovalQ = 'SELECT `join_approval_date` 
				           FROM `th_members`
				           WHERE `idx` = ?
				           FOR UPDATE';

        $rJoinApprovalResult = $db->execute($rJoinApprovalQ, $memberIdx);
        if ($rJoinApprovalResult === false) {
            throw new RollbackException('가입승인일자를 조회하면서 오류가 발생했습니다.');
        }

        // 가입 승인이 되 경우 진행하지 않을 것
        $joinApprovalDate = $rJoinApprovalResult->fields['join_approval_date'];
        if (!empty($joinApprovalDate)) {
            throw new RollbackException('이미 승인 된 회원입니다.');
        }

        // 가입 승인 처리
        $uMemberQ = 'UPDATE `th_members` 
					 SET `join_approval_date` = CURDATE() 
					 WHERE `idx` = ?';

        $uMemberResult = $db->execute($uMemberQ, $memberIdx);

        $memberAffectedRow = $db->affected_rows();
        if ($memberAffectedRow < 0) {
            throw new RollbackException('가입 승인 처리하는 중에 오류가 발생했습니다.');
        }

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
        }
    }
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout.html.php'; // 전체 레이아웃