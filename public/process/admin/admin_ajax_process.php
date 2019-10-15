<?php
	/**
	 * ajax 통신 (계정 관련 중복검사)
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
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/AdminClass.php';

	try {
		if (isset($_POST['mode'])) {
			$mode = htmlspecialchars($_POST['mode']);
		} else {
			$mode = htmlspecialchars($_GET['mode']);
		}

		if ($mode == 'overlapCheck') {
			$param = htmlspecialchars($_POST['val']);
        
			if (isset($_POST['detail_mode'])) {
				$detailMode = htmlspecialchars($_POST['detail_mode']);
			} else {
				$detailMode = htmlspecialchars($_GET['detail_mode']);
			}

			$adminClass = new AdminClass($db);
			$resultOverlap = 0;

			if ($detailMode == 'getUserId') {
				$resultOverlap = $adminClass->getIdOverlapCount($param);
			} else if ($detailMode == 'getUserEmail') {
				$resultOverlap = $adminClass->getEmailOverlapCount($param);
			} else if ($detailMode == 'getUserPhone') {
				$resultOverlap = $adminClass->getPhoneOverlapCount($param);
			}

			if ($resultOverlap > 0) {
				throw new Exception('아이디/이메일/핸드폰은 중복 될 수 없습니다.');
			} else {
				$result = [
					'result'=>0,
					'detail_mode'=>$detailMode
				];
			}
		}
	} catch (Exception $e) {
		$result = [
			'result'=>1,
			'detail_mode'=>$detailMode,
			'errorMessage'=>$e->getMessage()
		];
	} finally {
		if  ($connection === true) {
			$db->close();
		}

		echo json_encode($result);
		exit;
	}
