<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 공용함수
	 */

	function checkMemberFormValidate($postData)
	{
		/**
		 * @author: LeeTaeHee
		 * @param: 입력 폼 배열 데이터
		 * @brief: 폼 데이터 유효성 검증(로그인/회원가입).
		 * @return: boolean
		 */
		$repEmail = "/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i";

		if (empty($postData['id'])) {
			return false;
		}

		if (empty($postData['password'])) {
			return false;
		}

		if (!empty($postData['mode']) && $postData['mode']=='add') {

			if (empty($postData['repassword'])) {
				return false;
			}

			if ($postData['password']!=$postData['repassword']) {
				return false;
			}

			if (empty($postData['name'])) {
				return false;
			}

			if (empty($postData['email'])) {
				return false;
			}else{
				if (preg_match($repEmail,$postData['email'])==false) {
					return false;
				}
			}

			if (empty($postData['phone'])) {
				return false;
			}

			if (empty($postData['birth'])) {
				return false;
			}

			if (empty($postData['sex'])) {
				return false;
			}

			if (empty($postData['account_no'])) {
				return false;
			}

			if (empty($postData['account_bank'])) {
				return false;
			}
		}

		return true;
	}

	function getIsCheckAccountOverlap($pdo=null, $postData)
	{
		/**
		 * @author: LeeTaeHee
		 * @param: $pdo 객체, 폼 데이터
		 * @brief: 회원 가입 시 중복 체크(아이디,이메일, 핸드폰)
		 * @return: 0- 실패, 1- 성공
		 */
	
		$query = 'SELECT count(id) cnt FROM `imi_members` WHERE `id` = :id';
		$stmt = $pdo->prepare($query);
		$stmt->bindValue(':id', $postData['id']);
		$result = $stmt->execute();

		if ($result == true) {
			$idResult = $stmt->fetch();	

			if ($idResult['cnt'] > 0) {
				return false; // id 중복체크
			} 
		} else {
			return false;
		}
		
		$query = 'SELECT count(email) cnt FROM `imi_members` WHERE `email` = :email';
		$stmt = $pdo->prepare($query);
		$stmt->bindValue(':email', setEncrypt($postData['email']));
		$result = $stmt->execute();
		
		if ($result == true) { 
			$emailResult = $stmt->fetch();

			if ($emailResult['cnt'] > 0) {
				return false; // email 중복체크
			} 
		} else {
			return false;
		}

		$query = 'SELECT count(phone) cnt FROM `imi_members` WHERE `phone` = :phone';
		$stmt = $pdo->prepare($query);
		$stmt->bindValue(':phone', setEncrypt($postData['phone']));
		$result = $stmt->execute();
		
		if ($result == true) {
			$phoneResult = $stmt->fetch();

			if ($phoneResult['cnt'] > 0) {
				return false; // phone 중복체크
			}
		} else {
			return false;
		}

		return true;
	}

	function getLowGrade($pdo)
	{
		/**
		 * @author: LeeTaeHee
		 * @param: $pdo 객체
		 * @brief: 등급 테이블(imi_member_grade)에서 가장 낮은 등급을 가져온다..
		 * @return: 0- 실패, 1- 성공
		 */

		 $result = false;

		 $query = 'SELECT MIN(grade_code) grade_code 
				   FROM `imi_member_grades` 
				   WHERE `member_type` = "member"';
		 $stmt = $pdo->prepare($query);
		 $result = $stmt->execute();
		 
		 $grade_code = 3;
		 if ($result == true) {
			$memberData = $stmt->fetch();
			$grade_code = $memberData['grade_code'];
		 }

		 return $grade_code;
	}

	function insertActivityHistory($pdo, $param)
	{
		/**
		 * @author: LeeTaeHee
		 * @param: $pdo 객체, member_idx와 변동사유 배열로 받기.
		 * @brief: imi_member_activity_history 테이블에 insert.
		 * @return: 0- 실패, 1- 성공
		 */

		 $result = false;
		 
		 $query = 'SELECT `id`,`grade_code`,`admin_grade` 
					FROM `imi_members` 
					where idx = :idx
				  ';
		 $stmt = $pdo->prepare($query);
		 $stmt->bindValue(':idx',$param['idx']);
		 $result = $stmt->execute();
		 
		 if ($result == true) {
			$member = $stmt->fetch();

			$query = 'INSERT INTO `imi_member_activity_history` SET
						`member_idx` = :idx,
						`grade_code` = :grade_code,
						`admin_grade` = :admin_grade,
						`change_memo` = :change_memo,
						`change_datetime` = NOW()
					 ';
			$stmt = $pdo->prepare($query);
			$stmt->bindValue(':idx', $param['idx']);
			$stmt->bindValue(':grade_code', $member['grade_code']);
			$stmt->bindValue(':admin_grade', $member['admin_grade']);
			$stmt->bindValue(':change_memo', $param['changeMsg']);
			$result = $stmt->execute();
		 }
		 return $result;
	}

	function getValidPeriodExcessMember($pdo)
	{
		/**
		 * @author: LeeTaeHee
		 * @param: $pdo 객체
		 * @brief: 유효기간 초과 한 데이터 리턴
		 * @return: 쿼리 실행결과, 데이터를 배열로 리턴
		 */
		 $today = date('Y-m-d');

		 $query = 'SELECT `member_idx`, `idx`, `charge_cost`
				   FROM `imi_mileage_charge`
				   WHERE `mileage_idx` <> :mileage_idx
				   AND `is_expiration` = :is_expiration
				   AND `expiration_date` <= :expiration_date
				   ORDER BY `member_idx` ASC
				  ';
		$stmt = $pdo->prepare($query);
		$stmt->bindValue(':mileage_idx', 5);
		$stmt->bindValue(':is_expiration', 'N');
		$stmt->bindValue(':expiration_date', $today);
		$result = $stmt->execute();

		return ['isSuccess'=>$result, 'data'=>$stmt->fetchAll() ];
	}

	function deleteValidPeriodExcessData($pdo, $param)
	{
		/**
		 * @author: LeeTaeHee
		 * @param: $pdo 객체, 삭제 해야 할 데이터 정보 
		 * @brief: 유효기간 초과한 데이터 삭제 
		 * @return: 성공시 true, 실패시 false
		 */
		 $processDate = date('Y-m-d');
		 $loopSuccessCount = 0;
		
		 $result = true;
		 $insertMileageChangeResult = true;
		 $udpateMileageChargeResult = true;
		 $selectTotalMileageResult = true;
		 $updateMemberMileageResult = true;

		 if (count($param) > 0) {
			 for ($i = 0; $i < count($param); $i++) {
				 $query = 'INSERT INTO `imi_mileage_change` SET 
							`member_idx` = :member_idx,
							`charge_idx` = :charge_idx,
							`charge_cost` = :charge_cost,
							`charge_status` = :charge_status,
							`process_date` = :process_date
						  ';
				 $stmt = $pdo->prepare($query);
				 $stmt->bindValue(':member_idx', $param[$i]['member_idx']);
				 $stmt->bindValue(':charge_idx', $param[$i]['idx']);
				 $stmt->bindValue(':charge_cost', $param[$i]['charge_cost']);
				 $stmt->bindValue(':charge_status', 4);
				 $stmt->bindValue(':process_date', $processDate);
				 $insertMileageChangeResult = $stmt->execute();

				 if ($insertMileageChangeResult == true) {
					 $loopSuccessCount = $loopSuccessCount + 1;
				 } else {
					 return false;
				 }
			 }

			 if ($loopSuccessCount > 0) {
				 // 회원별 유효기간 초과한 데이터에 대해 합계금액 산출.
				 $query = 'SELECT `member_idx`,
								  SUM(`charge_cost`) `sum_charge_cost` 
						   FROM `imi_mileage_charge` 
						   WHERE `mileage_idx` <> :mileage_idx
						   AND `is_expiration` = :is_expiration 
						   AND `expiration_date` <= :expiration_date
						   GROUP BY `member_idx`
						   ORDER BY `member_idx` ASC
						  ';
				 $stmt = $pdo->prepare($query);
				 $stmt->bindValue(':mileage_idx', 5);
				 $stmt->bindValue(':is_expiration', 'N');
				 $stmt->bindValue(':expiration_date', $processDate);
				 $selectTotalMileageResult = $stmt->execute();

				 if($selectTotalMileageResult == false) {
					 return false;
				 } 
			 } else { 
				 return false;
			 }

			 if ($selectTotalMileageResult == true) {
				 $data = $stmt->fetchAll();
				 // imi_mileage_charge 테이블 업데이트
				 $query = 'UPDATE `imi_mileage_charge`
						   SET `is_expiration` = :is_expiration,
							   `spare_cost` = `spare_cost` * :spare_cost,
							   `charge_status` = :charge_status
						   WHERE `mileage_idx` <> :mileage_idx
						   AND `is_expiration` = :is_expiration_value
						   AND `expiration_date` <= :expiration_date
						  ';
				 $stmt = $pdo->prepare($query);
				 $stmt->bindValue(':is_expiration', 'Y');
				 $stmt->bindValue(':spare_cost', -1);
				 $stmt->bindValue(':charge_status', 4);
				 $stmt->bindValue(':mileage_idx', 5);
				 $stmt->bindValue(':is_expiration_value', 'N');
				 $stmt->bindValue(':expiration_date', $processDate);
				 $udpateMileageChargeResult = $stmt->execute();

				 if($udpateMileageChargeResult == false) {
					 return false;
				 } 
			 } else { 
				 return false;
			 }

			 if ($udpateMileageChargeResult == true) {
				 $loopSuccessCount = 0;
				 for ($i = 0; $i < count($data); $i++) { 
					 $query = ' UPDATE `imi_members` SET
								 `mileage` = `mileage` - :spare_total_mileage
								 WHERE `idx` = :idx
							  ';
					 $stmt = $pdo->prepare($query);
					 $stmt->bindValue(':spare_total_mileage', $data[$i]['sum_charge_cost']);
					 $stmt->bindValue(':idx', $data[$i]['member_idx']);
					 $updateMemberMileageResult = $stmt->execute();

					 if ($updateMemberMileageResult == true) {
						 $loopSuccessCount = $loopSuccessCount + 1;
					 } else { 
						 return false;
					 }
				 }
			 } else { 
				 return false;
			 }

			 if ($loopSuccessCount > 0) {
				 $result = true;
			 }
		 } else {
			$result = false;
		 }

		 return $result;
	}

	function setEncrypt($column)
	{
		/**
		 * @author: LeeTaeHee
		 * @param: 패스워드
		 * @brief: 암호화
		 * @return: 암호화 값
		 */
		 return openssl_encrypt($column,ENCRYPT_TYPE,ENCRYPT_KEY,false,str_repeat(chr(0),16));
	}

	function setDecrypt($column)
	{
		/**
		 * @author: LeeTaeHee
		 * @param: 패스워드
		 * @brief: 암호화
		 * @return: 암호화 값
		 */
		 return openssl_decrypt($column,ENCRYPT_TYPE,ENCRYPT_KEY,false,str_repeat(chr(0),16));
	}

	function alertMsg($url)
	{
		/**
		 * @author: LeeTaeHee
		 * @param: URL
		 * @brief: 이동 할 수 있는 함수. 추후 옵션에 따라 분기 처리 할 예정
		 * @return: 없음.
		 */
		 echo "<script>
				window.location  = '".$url."';
			   </script>
			  ";
		 exit;
	}