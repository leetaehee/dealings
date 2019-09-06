<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 
		1. 마일리지 유효기간 관리
		2. 유효기간이 넘은 경우 사용금액 마이너스 처리 시키기 
		3. 유효기간 초과하여 마이너스 되는 데이터는 반드시 이력테이블에 넣을 것
		4. imi_mileage_charge : 유효기간 초과여부(is_expiration='Y'), 비용 마이너스 처리(spare_cost) 
		5. imi_mileage_change : 회원 fk, imi_mileage_charge.fk, 비용, 사유, 처리일자
		6. imi_members: mileage 업데이트 (현재 잔액-마이너스 처리시기전 합계)
		7. crontab 이용해서 자동으로 시작되도록 설정 할 것
	*/

	// crontab 사용을 위해서 $_SERVER['DOCUMENT_ROOT'] 대신에 __DIR__ 
	$topDirectory = __DIR__ . '/../../../';

	// 환경설정
	include_once $topDirectory . 'configs/config.php'; 
	// 메세지
	include_once $topDirectory . 'messages/message.php';
	// 공통함수
	include_once $topDirectory . 'includes/function.php';
	// PDO 객체 생성
	include_once $topDirectory . 'includes/databaseConnection.php';

	try {
		$returnUrl = SITE_DOMAIN; // return url 
		$result = true; // 쿼리결과

		// 유효기간 초과한 회원 데이터 받아오기.
		$result = getValidPeriodExcessMember($pdo);

		if ($result['isSuccess']==true) {
			$members = $result['data'];			
			//유효기간 초과 데이터 정리 

			// 트랜잭션 시작
			$pdo->beginTransaction();
			$result = deleteValidPeriodExcessData($pdo, $members);
			$pdo->commit();
		}
		 
		if ($result == true) { 
			echo '유효기간 만료 된 회원들의 마일리지 변동하였습니다.';
		} else {
			if (count($members) == 0) {
				echo '유효기간 만료 된 회원들이 존재하지 않습니다.';
			} else { 
				echo '오류입니다.'; 
			}
		}
	} catch (PDOException $e) {
		$pdo->rollback();
		$output = DB_CONNECTION_ERROR_MESSAGE.$e->getMessage().', 위치: '.$e->getFile().':'.$e->getLine();
		echo $output;
	}