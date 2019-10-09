<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 회원가입 메일 승인 화면 
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수
	
	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	// Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php';

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
			$idx = htmlspecialchars($_GET['idx']);
		} else {
			$idx = htmlspecialchars($_POST['idx']);
		}

		if (!empty($idx)) {
			$db->startTrans();

			$memberClass = new MemberClass($db);
			$join_approval_date = $memberClass->getJoinApprovalMailDate($idx);
			if ($join_approval_date !== null) {
				throw new RollbackException('이미 가입승인 메일을 통해 승인 하였습니다!');
			}else{	
				$updateApprovalResult = $memberClass->updateJoinApprovalMailDate($idx);
				if ($updateApprovalResult < 1) {
					throw new RollbackException('가입 승인 수정 중에 오류가 발생했습니다.');
				} else {
					$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/join_approval.html.php';
				}

				$db->completeTrans();
			}
		} else {
			throw new Exception('잘못된 접근입니다!');
		}
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