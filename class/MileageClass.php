<?php
	Class MileageClass 
	{
		private $db = null;

		private function checkConnection()
		{
			if(!is_object($this->db)) {
				return false;
			}
			return true;
		}
		
		public function __construct($db) 
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 커넥션 파라미터
			 * @brief: 데이터베이스 커넥션 생성 
			 */
			$this->db = $db;
		}

		public function checkChargeFormValidate($postData)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 폼 데이터
			 * @brief: 유효성 검증(마일리지 충전폼)
			 * @return: true/false, error-message 배열
			 */
			
			if (isset($postData['account_bank']) && empty($postData['account_bank'])) {
				return ['isValid'=>false, 'errorMessage'=>'입금은행을 입력하세요'];
			}

			if (isset($postData['account_no']) && empty($postData['account_no'])) {
				return ['isValid'=>false, 'errorMessage'=>'계좌번호를 입력하세요'];
			}

			if (isset($postData['charge_cost']) && empty($postData['charge_cost'])) {
				return ['isValid'=>false, 'errorMessage'=>'입금금액을 입력하세요'];
			}

			if (isset($postData['charge_name']) && empty($postData['charge_name'])) {
				return ['isValid'=>false, 'errorMessage'=>'입금자를 입력하세요.'];
			}

			return ['isValid'=>true, 'errorMessage'=>''];
		}

		public function getExpirationDay($mileageType)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 마일리지 타입 
			 * @brief: 마일리지 만료 주기 가져오기
			 * @return: string
			 */
			
			$query = 'SELECT `expiration_day`,`period` FROM `imi_mileage` WHERE `idx` = ? FOR UPDATE';
			$result = $this->db->execute($query,$mileageType);

			if ($result == false) {
				return false;
			}

			$mileageData = [
					'period'=>$result->fields['period'],
					'day'=>$result->fields['expiration_day']
				];

			return $mileageData;
		}

		public function insertMileageCharge($param)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 마일리지 충전에 필요한 데이터 배열
			 * @brief: 마일리지 충전
			 * @return: int
			 */

			$query = 'INSERT INTO `imi_mileage_charge` SET
						`member_idx` = ?,
						`charge_infomation` = ?,
						`charge_account_no` = ?,
						`charge_cost` = ?,
						`spare_cost` = ?,
						`charge_name` = ?,
						`mileage_idx` = ?,
						`charge_date` = ?,
						`charge_status` = ?';
			
			if(isset($param['expirationDate'])){
				$query .= ',`expiration_date` = ?';
			}

			$result = $this->db->execute($query, $param);
			$insertId = $this->db->insert_id(); // 추가

			if ($insertId < 1) {
				return false;
			}

			return $insertId;
		}

		public function insertMileageChange($param)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 회원PK, 충전PK, 금액, 상태, 처리일자
			 * @brief: 충전을 제외한 출금,취소 등 데이터 삽입
			 * @return: int
			 * @이 조건 삭제. `charge_idx` = ?,
			 */

			$query = 'INSERT INTO `imi_mileage_change` SET
						`member_idx` = ?,
						`mileage_idx` = ?,
						`charge_cost` = ?,
						`charge_account_no` = ?,
						`charge_infomation` = ?,
						`charge_name` = ?,
						`charge_status` = ?,
						`process_date` = CURDATE()';
			
			$result = $this->db->execute($query, $param);
			$insertId = $this->db->insert_id(); // 추가

			if ($insertId < 1) {
				return false;
			}

			return $insertId;
		}

		public function getVirtualMileageMaxCharge($param)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 회원PK, 마일리지 타입
			 * @brief: 유효기간 지나지 않은 마일리지 합계 
			 * @return: int
			 */

			$param['is_expiration'] = 'N';
			
			$query = 'SELECT SUM(charge_cost) charge_cost
					  FROM `imi_mileage_charge`
					  WHERE `mileage_idx` = ?
					  AND `member_idx` = ? 
					  AND `is_expiration` = ?
					';
			$result = $this->db->execute($query,$param);

			if ($result == false) {
				return false;
			}
			
			return $result;
		}

		public function getVirtualMileageMaxWithdrawal($param)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 회원PK, 마일리지 타입
			 * @brief: 충전을 제외한 출금,취소 등 마일리지 합계 
			 * @return: int
			 */

			$param['charge_status'] = 3;
			
			$query = 'SELECT IFNULL(SUM(charge_cost),0) charge_cost
					  FROM `imi_mileage_change`
					  WHERE `mileage_idx` = ?
					  AND `member_idx` = ? 
					  AND `charge_status` <> ?
					';
			$result = $this->db->execute($query,$param);

			if ($result == false) {
				return false;
			}
			
			return $result;
		}

		public function getMileageCharge($idx)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 회원PK
			 * @brief: 충전내역 출력
			 * @return: array
			 */

			$param = [
					'memberIdx'=>$idx,
					'charge_status'=>3,
					'is_expiration'=>'N'
				];

			$query = 'SELECT `imc`.`idx`,
							 `imc`.`charge_cost`,
							 `imc`.`charge_date`,
							 `imc`.`charge_infomation`,
							 `imc`.`charge_account_no`,
							 `imc`.`charge_name`,
							 `imc`.`charge_status`,
							 `code`.`mileage_name`,
							 `im`.`charge_taget_name`
					  FROM `imi_mileage_charge` `imc`
						INNER JOIN `imi_mileage_code` `code`
							ON `imc`.`charge_status` = `code`.`mileage_code`
						INNER JOIN `imi_mileage` `im`
							ON `imc`.`mileage_idx` = `im`.`idx`
					  WHERE `imc`.`member_idx` = ?
					  AND `imc`.`charge_status` = ?
					  AND `imc`.`is_expiration` = ?
					';
			$result = $this->db->execute($query,$param);

			if ($result == false) {
				return false;
			}
			
			return $result;
		}

		public function getMileageWithdrawal($idx)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 회원PK
			 * @brief: 충금내역 출력
			 * @return: array
			 */

			$param = [
					'memberIdx'=>$idx,
					'charge_status'=>3
				];

			$query = 'SELECT `imc`.`idx`,
							 `imc`.`charge_cost`,
							 `imc`.`process_date`,
							 `imc`.`charge_account_no`,
							 `imc`.`charge_infomation`,
							 `imc`.`charge_name`,
							 `imc`.`charge_status`,
							 `code`.`mileage_name`,
							 `im`.`charge_taget_name`
					  FROM `imi_mileage_change` `imc`
						INNER JOIN `imi_mileage_code` `code`
							ON `imc`.`charge_status` = `code`.`mileage_code`
						INNER JOIN `imi_mileage` `im`
							ON `imc`.`mileage_idx` = `im`.`idx`
					  WHERE `imc`.`member_idx` = ?
					  AND `imc`.`charge_status` <> ?
					';
			$result = $this->db->execute($query,$param);

			if ($result == false) {
				return false;
			}
			
			return $result;
		}

	}