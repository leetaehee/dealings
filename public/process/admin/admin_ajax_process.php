<?php
	/**
	 * 계정중복확인(ajax)
	 */

	header("Content-Type: application/json"); 
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	try {
        $param = htmlspecialchars($_POST['val']);

        if (isset($_POST['detail_mode'])) {
            $detailMode = htmlspecialchars($_POST['detail_mode']);
        } else {
            $detailMode = htmlspecialchars($_GET['detail_mode']);
        }

        $resultOverlap = 0;

        $rAdminQ = '';
        if ($detailMode == 'getUserId') {
            // 아이디 중복 체크
            $rAdminQ = 'SELECT count(`id`) cnt FROM `th_admin` WHERE `id` = ?';
        } else if ($detailMode == 'getUserEmail') {
            // 암호화
            $param = setEncrypt($param);

            // 이메일 중복체크
            $rAdminQ = 'SELECT count(`email`) cnt FROM `th_admin` WHERE `email` = ?';
        }  else if ($detailMode == 'getUserPhone') {
            // 암호화
            $param = setEncrypt($param);

            // 핸드폰 중복체크
            $rAdminQ = 'SELECT count(`phone`) cnt FROM `th_admin` WHERE `phone` = ?';
        }

        $rAdminChkResult = $db->execute($rAdminQ, $param);
        if ($rAdminChkResult === false) {
            throw new Exception('계정 정보를 조회 하면서 오류가 발생하였습니다.');
        }

        $resultOverlap = $rAdminChkResult->fields['cnt'];
        if ($resultOverlap > 0) {
            $result = [
                'result'=> 1,
                'detail_mode'=> $detailMode
            ];
        } else {
            $result = [
                'result'=> 0,
                'detail_mode'=> $detailMode
            ];
        }
	} catch (Exception $e) {
		$result = [
			'result'=> 1,
			'detail_mode'=> $detailMode,
			'errorMessage'=> $e->getMessage()
		];
	} finally {
		if  ($connection === true) {
			$db->close();
		}

		echo json_encode($result);
		exit;
	}
