<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 상품권 거래 상태 변경 (판매/구매)
	 */

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수

	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

    // Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/DealingsClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MileageClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/DealingsCommissionClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/SellItemClass.php';

	// Exception 파일 
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../Exception/RollbackException.php';

	try {
		$returnUrl = SITE_DOMAIN; // 리턴되는 화면 URL 초기화.
        $alertMessage = '';

		if ($connection === false) {
           throw new Exception('데이터베이스 접속이 되지 않았습니다. 관리자에게 문의하세요');
        }

		$dealingsClass = new DealingsClass($db);

		$_POST['dealings_type'] = htmlspecialchars($_POST['dealings_type']);
		$postData = $_POST;

		// returnURL
		$returnUrl = SITE_DOMAIN . '/voucher_dealings.php';

		$dealingsTypeArray = ['구매', '판매'];
		if (!in_array($postData['dealings_type'], $dealingsTypeArray)) {
			throw new Exception('유효하지 않은 거래 타입입니다. 다시 시도하세요.');
		}

		$db->startTrans();
		
		$param = [
			'idx'=>$_SESSION['dealings_idx'],
			'is_del'=>'N',
			'dealinges_status'=>$_SESSION['dealings_status'],
		];

		$existCount = $dealingsClass->isDealingsDataExist($param);
		if ($existCount === false){
			throw new RollbackException('거래 데이터 이중등록 체크에 오류가 발생했습니다.');
		}

		if($existCount === 0){
			throw new RollbackException('해당 데이터는 대기상태가 아닙니다!');
		}

		// 다음 거래상태 구하기
		$statusData = [
			'dealings_status'=>$_SESSION['dealings_status']
		];

		$nextStatus = $dealingsClass->getNextDealingsStatus($statusData);
		if ($nextStatus === false) {
			throw new RollbackException('거래 상태에 오류가 발생했습니다.');
		}

		$dealingsExistCount = $dealingsClass->isExistDealingsIdx($_SESSION['dealings_idx']);
		if ($dealingsExistCount === false) {
			throw new RollbackException('거래 유저 테이블에 오류가 발생했습니다.');
		}

		if ($_SESSION['dealings_status'] == 1 && $dealingsExistCount == 0) {
			// 판매대기, 구매대기의 경우 거래유저테이블에 데이터 생성	
			$userData = [
				'dealings_idx'=>$_SESSION['dealings_idx'],
				'dealings_writer_idx'=>$_SESSION['dealings_writer_idx'],
				'dealings_member_idx'=>$_SESSION['idx'],
				'dealings_status'=>$nextStatus,
				'dealings_type'=>$postData['dealings_type']
			];

			$insertResult = $dealingsClass->insertDealingsUser($userData);
			if ($insertResult < 1) {
				throw new RollbackException('거래 유저 생성 실패하였습니다.');
			}
		} else {
			// 거래유저테이블에 데이터 수정
			$userData = [
				'dealings_status'=>$nextStatus,
				'dealings_idx'=>$_SESSION['dealings_idx']
			];

			$updateResult = $dealingsClass->updateDealingsUser($userData);
			if ($updateResult < 1) {
				throw new RollbackException('거래 유저 수정 실패하였습니다.');
			}
		}

		$dealingsParam = [
			'nextStatus'=>$nextStatus,
			'idx'=>$_SESSION['dealings_idx']
		];
		
		// 거래테이블 상태변경 
		$updateDealingsStatus = $dealingsClass->updateDealingsStatus($dealingsParam);
		if ($updateDealingsStatus < 1) {
			throw new RollbackException('거래 상태 정보 수정 실패하였습니다.');
		}

		// 처리절차 생성하기
		$processData = [
			'dealings_idx'=>$_SESSION['dealings_idx'],
			'dealings_status_idx'=>$nextStatus
		];

		$insertProcessResult = $dealingsClass->insertDealingsProcess($processData);
		if ($insertProcessResult < 1) {
			throw new RollbackException('거래 처리과정 생성 실패하였습니다.');
		}

		$_SESSION['dealings_writer_idx'] = '';
		$_SESSION['dealings_idx'] = '';
		$_SESSION['dealigng_status'] = '';

		$returnUrl = SITE_DOMAIN.'/voucher_dealings.php';
		$alertMessage = '정상적으로 거래 상태가 변경되었습니다.';

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