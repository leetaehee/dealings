<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: ajax 통신 
	 */

	header("Content-Type: application/json"); 

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/mailer.lib.php'; // PHP메일보내기

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php'; // adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php'; // adodb

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php'; // Class 파일

	$returnUrl = ''; // 리턴되는 화면 URL 초기화.
	$mode = isset($_POST['mode']) ?  $_POST['mode'] : $_GET['mode'];

	if ($mode == 'overlapCheck') {
		/**
		 * @author: LeeTaeHee
		 * @brief: 회원 가입 시 중복 체크(아이디,이메일,핸드폰)
		 */
		$param = $_POST['val'];
		$detail_mode = isset($_POST['detail_mode']) ?  $_POST['detail_mode'] : $_GET['detail_mode'];

		$memberClass = new MemberClass($db);

		if ($detail_mode == 'getUserId') {
			$resultOverlap = $memberClass->getIdOverlapCount($param);
		} else if ($detail_mode == 'getUserEmail') {
			$resultOverlap = $memberClass->getEmailOverlapCount($param);
		} else if ($detail_mode == 'getUserPhone') {
			$resultOverlap = $memberClass->getPhoneOverlapCount($param);
		}

		if ($resultOverlap > 0) {
			$result = ['result'=>1, 'detail_mode'=>$detail_mode];
		} else {
			$result = ['result'=>0, 'detail_mode'=>$detail_mode];
		}

		echo json_encode($result);
		exit;
	}