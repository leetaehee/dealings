<?php
    /**
	 * @file MileageClass.php
	 * @brief 마일리지에 대한 클래스
	 * @author 이태희
	 */
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
		
		 /**
         * @brief: 마일리지 타입 코드에 대해 테이블 컬럼 값으로 반환  
         * @param: 마일리지 타입 
		 * @return: string
         */
		private function getMileageTypeColumn($mileageTypeCode){
			switch($mileageTypeCode){
				case 1:
					$colName = 'card_sum';
					break;
				case 2:
					$colName = 'phone_sum';
					break;
				case 3:
					$colName = 'culcture_voucher_sum';
					break;
				case 5:
					$colName = 'virtual_account_sum';
					break;
				case 7:
					$colName = 'dealings_sum';
					break;
			}
			return $colName;
		}
		
        /**
         * @brief: 데이터베이스 커넥션 생성 
         * @param: 커넥션 파라미터
         */
		public function __construct($db) 
        {
			$this->db = $db;
		}

        /**
         * @brief: 유효성 검증(마일리지 충전폼)
         * @param: 폼 데이터
         * @return: array
         */
		public function checkChargeFormValidate($postData)
		{	
			if (isset($postData['account_bank']) && empty($postData['account_bank'])) {
				return ['isValid'=>false, 'errorMessage'=>'입금은행을 입력하세요'];
			}

			if (isset($postData['account_no']) && empty($postData['account_no'])) {
				return ['isValid'=>false, 'errorMessage'=>'계좌번호를 입력하세요'];
			}

			if (isset($postData['charge_cost']) && empty($postData['charge_cost'])) {
				return ['isValid'=>false, 'errorMessage'=>'금액을 입력하세요'];
			}

			if (isset($postData['charge_name']) && empty($postData['charge_name'])) {
				return ['isValid'=>false, 'errorMessage'=>'입금자를 입력하세요.'];
			}

			return ['isValid'=>true, 'errorMessage'=>''];
		}

        /**
         * @brief: 마일리지 만료 주기 가져오기
         * @param: 마일리지 타입
         * @return: string
         */
		public function getExpirationDay($mileageType)
		{
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
        
        /**
         * @brief: 마일리지 충전
         * @param: 마일리지 충전에 필요한 데이터 배열
         * @return: int
         */
		public function insertMileageCharge($param)
		{
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

        /**
         * @brief: 충전을 제외한 출금,취소 등 데이터 삽입
         * @param: 회원PK, 충전PK, 금액, 상태, 처리일자을 담은 array
         * @return: int
         */
		public function insertMileageChange($param)
		{
			$count = count($param);

			if ($count > 0) {
				for ($i = 0; $i < $count; $i++) {
					$query = 'INSERT INTO `imi_mileage_change` SET
								`member_idx` = ?,
								`mileage_idx` = ?,
								`charge_account_no` = ?,
								`charge_infomation` = ?,
								`charge_name` = ?,
								`charge_status` = ?,
								`process_date` = ?,
								`charge_idx` = ?,
								`charge_cost` = ?
							';

					$result = $this->db->execute($query,$param[$i]);
					$insertId = $this->db->insert_id(); // 추가

					if ($insertId < 1) {
						return false;
					}
				}
			} else {
				return false;
			}

			return $insertId;
		}

		/**
         * @brief: 거래를 통해 사용한 마일리지 내역 저장
         * @param: 거래시 거래제안자, 거래자, 상태코드, 비용을 담은 array
         * @return: int
         */
		public function insertDealingsMileageChange($param)
		{
			$count = count($param);

			if ($count > 0) {
				for ($i = 0; $i < $count; $i++) {
					$query = 'INSERT INTO `imi_dealings_mileage_change` SET
								`dealings_idx` = ?,
								`dealings_writer_idx` = ?,
								`dealings_member_idx` = ?,
								`charge_idx` = ?,
								`dealings_status_code` = ?,
								`dealings_date` = CURDATE(),
								`dealings_money` = ?';

					$result = $this->db->execute($query,$param[$i]);
					$insertId = $this->db->insert_id(); // 추가

					if ($insertId < 1) {
						return false;
					}
				}
			} else {
				return false;
			}

			return $insertId;
		}
		
		/**
         * @brief: 가상계좌를 제외한 마일리지 충전 데이터 중 유효기간 만료된 데이터 받아오기
         * @param: none
         * @return: array
         */
		public function getMileageExcessValidDateList()
		{
			$today = date('Y-m-d');
			$param = [$today];

			$query = 'SELECT `member_idx`,
							 `mileage_idx`,
							 `charge_account_no`,
							 `charge_infomation`,
							 `charge_name`,
							 `charge_status`,
							 `idx`,
							 `spare_cost`,
							 `expiration_date`
					  FROM `imi_mileage_charge`
					  WHERE `mileage_idx` <> 5
					  AND `charge_status` = 3
					  AND `expiration_date` < ?
					  ORDER BY `member_idx` ASC, `expiration_date` ASC
					  FOR UPDATE';
			
			$result = $this->db->execute($query,$param);

			if ($result === false) {
				return false;
			}
			
			return $result;
		}

		/**
         * @brief: 가상계좌로 충전된 마일리지 리스트(for update 적용- 트랜잭션 처리 필요함)
         * @param: 회원PK, 마일리지 타입
         * @return: array
         */
		public function getVirutalAccountWithdrawalPossibleList($param)
		{
			$query = 'SELECT `idx`,
							 `charge_cost`,
							 `spare_cost`
					  FROM `imi_mileage_charge`
					  WHERE `mileage_idx` = ?
					  AND `member_idx` = ?
					  AND `charge_status` = ?
					  AND `spare_cost` > 0
					  ORDER BY `charge_date` ASC
					  FOR UPDATE
					';
			$result = $this->db->execute($query,$param);

			if ($result == false) {
				return false;
			}
			
			return $result;
		}

		/**
         * @brief: 충전된 마일리지 리스트(for update 적용- 트랜잭션 처리 필요함)
         * @param: 회원PK, 마일리지 타입
         * @return: array
         */
		public function getMileageWithdrawalPossibleList($param)
		{
			$query = 'SELECT `imc`.`idx`,
							 `imc`.`charge_cost`,
							 `imc`.`spare_cost`,
							 `imc`.`mileage_idx`
					  FROM `imi_mileage_charge` `imc`
						INNER JOIN `imi_mileage` `im`
							ON `imc`.`mileage_idx` = `im`.`idx`
					  WHERE `imc`.`member_idx` = ?
					  AND `imc`.`charge_status` = ?
					  AND `imc`.`spare_cost` > 0
					  ORDER BY `im`.`priority` ,`imc`.`expiration_date` ASC, `imc`.`charge_date` ASC
					  FOR UPDATE
					';

			$result = $this->db->execute($query,$param);

			if ($result == false) {
				return false;
			}
			
			return $result;
		}
		
		/**
         * @brief: 출금시 마일리지 충전내역에서 차감해야 할 정보 산출 (Key 정보)
         * @param: 충전금액과 키값을 배열로 전달
         * @return: array
         */
		public function getMildateChargeInfomationData($list,$chargeCost)
		{
			$count = $list->recordCount();
			$remainCost = $chargeCost;

			if ($count > 0) {
				foreach ($list as $key => $value){
					$useCost = 0;
					$tmpCost = $value['spare_cost'] - $remainCost;

					if ($tmpCost < 0) {
						$useCost = $value['spare_cost'];
						$remainCost -= $value['spare_cost'];
					}else {
						$useCost += $remainCost;
						$remainCost -= $remainCost;
					}
					
					// charge_status 변경 시 호출하는 영역에서 수정할것
					$updateData[] = [
						'spare_cost'=>$useCost,
						'use_cost'=>$useCost,
						'idx'=>$value['idx']
					];

					$mileageTypeData[] = [
						'use_cost'=>$useCost,
						'idx'=>$value['idx'],
						'mileage_idx'=>$value['mileage_idx']
					];

					if ($useCost==0) {
						array_pop($updateData);
					}

					if ($tmpCost > 0) {
						break;
					}
				}
			} else {
				return false;
			}
	
			return ['update_data'=>$updateData, 'mileage_type_data'=>$mileageTypeData];
		}
		
		/**
         * @brief: 두 개의 배열 내용 합치기(출금데이터)
         * @param: array 
         * @return: array
         */
		public function updateMileageArray($chargeData, $mileageChangeParam)
		{
			$count = count($chargeData);

			if ($count > 0) {
				for ($i = 0; $i < $count; $i++) {
					$changeData[] = [
							'member_idx'=>$mileageChangeParam['memberIdx'],
							'mileage_idx'=>$mileageChangeParam['mileageIdx'],
							'charge_account_no'=>$mileageChangeParam['accountNo'],
							'charge_infomation'=>$mileageChangeParam['accountBank'],
							'charge_name'=>$mileageChangeParam['chargeName'],
							'charge_status'=>$mileageChangeParam['chargeStatus'],
							'process_date'=>$mileageChangeParam['process_date'],
							'charge_idx'=>$chargeData[$i]['idx'],
							'charge_cost'=>$chargeData[$i]['spare_cost'],
						];
				}
			} else {
				return false;
			}
			return $changeData;
		}

		/**
         * @brief: 두 개의 배열 내용 합치기(거래 시 출금데이터)
         * @param: array 
         * @return: array
         */
		public function updateDealingsMileageArray($chargeData, $mileageChangeParam)
		{
			$count = count($chargeData);

			if ($count > 0) {
				for ($i = 0; $i < $count; $i++) {
					$changeData[] = [
						'dealings_idx'=>$mileageChangeParam['dealings_idx'],
						'dealings_writer_idx'=>$mileageChangeParam['dealings_writer_idx'],
						'dealings_member_idx'=>$mileageChangeParam['dealings_member_idx'],
						'charge_idx'=>$chargeData[$i]['idx'],
						'dealings_status_code'=>$mileageChangeParam['dealings_status_code'],
						'charge_idx'=>$chargeData[$i]['idx'],
						'charge_cost'=>$chargeData[$i]['spare_cost'],
					];
				}
			} else {
				return false;
			}
			return $changeData;
		}

		/**
         * @brief: 출금 시  충전정보 업데이트(imi_mileage_charge)
         * @param: 충전금액과 키값을 배열로 전달
         * @return: array
         */
		public function updateMileageCharge($param)
		{
			$count = count($param);
			
			if($count > 0){
				for ($i = 0; $i < $count; $i++){
					$query = 'UPDATE `imi_mileage_charge` SET
							   `spare_cost` = `spare_cost` - ?,
							   `use_cost` = `use_cost` + ?
							   WHERE `idx` = ?  
							';
					
					$result = $this->db->execute($query,$param[$i]);
                    $affected_row = $this->db->affected_rows();

					if ($affected_row < 1) {
						return false;
					}
				}
			} else {
				return false;
			}
			return $affected_row;
		}

        /**
         * @brief: 충전내역 출력 (충전,충전취소만 보여짐)
         * @param: 회원PK
         * @return: array
         */
		public function getMileageCharge($idx)
		{
			$param = [
					'memberIdx'=>$idx,
					'is_expiration'=>'N'
				];
		
			$query = 'SELECT `imc`.`idx`,
							 `imc`.`charge_cost`,
							 `imc`.`use_cost`,
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
					  AND `imc`.`charge_status` IN (1,3)
					  AND `imc`.`is_expiration` = ?
					  FOR UPDATE
					';
			$result = $this->db->execute($query,$param);

			if ($result === false) {
				return false;
			}
			
			return $result;
		}

        /**
         * @brief: 출금내역 출력
         * @param: 회원PK
         * @return: array
         */
		public function getMileageWithdrawal($idx)
		{
			$param = [
					'memberIdx'=>$idx
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
					  AND `imc`.`charge_status` IN (2,4,5)
					  FOR UPDATE
					';
			$result = $this->db->execute($query,$param);

			if ($result === false) {
				return false;
			}
			
			return $result;
		}

		/**
         * @brief: `imi_mileage_type_sum`에 회원에 대한 데이터 있는지 찾기
         * @param: 회원PK
         * @return: string
         */
		public function getMemberMileageTypeIdx($idx)
		{
			$query = 'SELECT `idx` FROM `imi_mileage_type_sum` WHERE `member_idx` = ? FOR UPDATE';
			$result = $this->db->execute($query,$idx);

			if ($result == false) {
				return false;
			}
			
			return $result->fields['idx'];
		}

		/**
         * @brief: 회원 마일리지별 데이터 생성 
         * @param: 회원 PK, 마일리지 타입, 누적값
         * @return: array 
         */
		public function mileageTypeInsert($mileageType, $mileageTypeParam)
		{
			// 마일리지 타입에 대해서 컬럼 지정
			$colName = $this->getMileageTypeColumn($mileageType);

			$query = "INSERT INTO `imi_mileage_type_sum` SET
						`member_idx` = ?,
						`{$colName}` = `{$colName}` + ?
					";

			$result = $this->db->execute($query, $mileageTypeParam);
			$insertId = $this->db->insert_id();

			if ($insertId < 1) {
				return false;
			}

			return $insertId;
		}

		/**
         * @brief: 개별 충전 마일리지 타입 누적 
         * @param: 회원 PK, 마일리지 타입, 값
         * @return: int 
         */
		public function mileageTypeChargeUpdate($mileageType, $mileageTypeParam)
		{
			// 마일리지 타입에 대해서 컬럼 지정
			$colName = $this->getMileageTypeColumn($mileageType);

			$query = "UPDATE `imi_mileage_type_sum` SET
						`{$colName}` = `{$colName}` + ?
						WHERE `member_idx` = ?
					";

			$result = $this->db->execute($query, $mileageTypeParam);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}
		
		/**
         * @brief: 개별 출금 마일리지 타입 감소 
         * @param: 회원 PK, 마일리지 타입, 값
         * @return: int 
         */
		public function mileageTypeWithdrawalUpdate($mileageType, $mileageTypeParam)
		{
			// 마일리지 타입에 대해서 컬럼 지정
			$colName = $this->getMileageTypeColumn($mileageType);
	
			$query = "UPDATE `imi_mileage_type_sum` SET
						`{$colName}` = `{$colName}` - ?
						WHERE `member_idx` = ?
					";

			$result = $this->db->execute($query, $mileageTypeParam);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}
			return $affected_row;
		}

		/**
         * @brief: 전체 마일리지를 가지고 계산할 경우 타입별로 감소 
         * @param: 회원 PK, 마일리지 배열 데이터를 담은 array
         * @return: int 
         */
		public function mileageAllTypeWithdrawalUpdate($purchaser_idx, $chargeData)
		{
			// 차감데이터 만큼 마일리지 타입에 대해서 컬럼 지정
			$count = count($chargeData);

			if ($count > 0) {
				for ($i = 0; $i < $count; $i++) {
					$param = [
						'use_cost'=>$chargeData[$i]['use_cost'],
						'member_idx'=>$purchaser_idx
					];
	
					$colName = $this->getMileageTypeColumn($chargeData[$i]['mileage_idx']);

					$query = "UPDATE `imi_mileage_type_sum` SET
								`{$colName}` = `{$colName}` - ?
								WHERE `member_idx` = ?
							";

					$result = $this->db->execute($query, $param);
					$affected_row = $this->db->affected_rows();

					if ($affected_row < 1) {
						return false;
					}
				}
			} else {
				return false;
			}
			return $affected_row;
		}

		/**
		 * @brief: 출금 가능한 마일리지 가져오기
		 * @param: 마일리지타입, 회원PK 을 담는 배열 
		 * @return: int 
		 */
		public function getAvailableMileage($mileageTypeParam)
		{
			$colName = $this->getMileageTypeColumn($mileageTypeParam['mileageType']);
			$memberIdx = $mileageTypeParam['idx'];

			$query = "SELECT `{$colName}` `{$colName}`
					  FROM `imi_mileage_type_sum`
					  WHERE `member_idx` = ?
					  FOR UPDATE";
			
			$result = $this->db->execute($query,$memberIdx);

			if ($result == false) {
				return false;
			}
			
			return $result->fields[$colName];
		}

		/**
		 * @brief: 충전내역 보여주기
		 * @param: 없음 
		 * @return: arrray 
		 */
		public function getChargeList()
		{
			$param = ['N',1];

			$query = 'SELECT `imc`.`idx`,
							 `imc`.`member_idx`,
							 `imc`.`charge_date`,
							 `imc`.`charge_cost`,
							 `imc`.`spare_cost`,
							 `imc`.`use_cost`,
							 `imc`.`charge_infomation`,
							 `imc`.`charge_account_no`,
							 `im`.`name`,
							 `im`.`phone`,
							 `im`.`id`,
							 `imcd`.`charge_taget_name`
					  FROM `imi_mileage_charge` `imc`
						INNER JOIN `imi_members` `im`
							ON `imc`.`member_idx` = `im`.`idx`
						INNER JOIN `imi_mileage` `imcd`
							ON `imc`.`mileage_idx` = `imcd`.`idx`
					  WHERE `imc`.`is_expiration` = ?
					  AND `imc`.`charge_status` <> ?
					  FOR UPDATE';
			
			$result = $this->db->execute($query,$param);
			if ($result == false) {
				return false;
			}
			
			return $result;
		}

		/**
		 * @brief: 유효기간 만료된 충전내역을 배열로 받기
		 * @param: 충전정보 PK, 상태정보를 array
		 * @return: array
		 */
		public function getExpirationArrayData($list)
		{
			$count = $list->recordCount();
			
			if ($count > 0) {
				foreach($list as $key => $value){
					$expirationData[] = [
							'member_idx'=>$value['member_idx'],
							'mileage_idx'=>$value['mileage_idx'],
							'charge_account_no'=>$value['charge_account_no'],
							'charge_infomation'=>$value['charge_infomation'],
							'charge_name'=>$value['charge_name'],
							'charge_status'=>4,
							'process_date'=>$value['expiration_date'],
							'charge_idx'=>$value['idx'],
							'spare_cost'=>$value['spare_cost'],
						];
				}
			} else {
				return false;
			}
			return $expirationData;
		}

		/**
		 * @brief: 충전내역 유효기간 만료여부 수정 
		 * @param: 충전정보 PK, 상태정보를 배열로 받음.
		 * @return: int
		 */
		public function updateExpirationDate($chargeData)
		{
			$count = count($chargeData);

			if ($count > 0) {
				for($i = 0; $i<$count; $i++){
					
					$param[] = [
							$chargeData[$i]['spare_cost'],
							$chargeData[$i]['spare_cost'],
							$chargeData[$i]['charge_idx']
						];

					// `expiration_date` = ?, 안해도됨 
					//`is_expiration` = ?, 마지막에..
					//`charge_status` = 4 마지막에

					$query = 'UPDATE `imi_mileage_charge` SET
							   `spare_cost` = `spare_cost` - ?,
							   `use_cost` = `use_cost` + ?
							  WHERE `idx` = ?';

					$result = $this->db->execute($query, $param[$i]);
					$affected_row = $this->db->affected_rows();

					if ($affected_row < 1) {
						return false;
					}
				}
			} else {
				return false; 
			}

			return $affected_row;
		}
		
		/**
		 * @brief: 현재일자보다 유효기간이 작은 경우 충전상태, 만료여부 변경 
		 * @param: 날짜, 충전상태 array 
		 * @return: int
		 */
		public function updateStatusByExpirationDate($param)
		{
			$query = 'UPDATE `imi_mileage_charge` SET
						`is_expiration` = ?,
						`charge_status` = ?
					  WHERE `expiration_date` < ?
					  AND `charge_status` in (3,6)';

			$result = $this->db->execute($query, $param);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}

		
		/**
		 * @brief: 충전내역 상태 변경하기(금액, 상태)
		 * @param: 충전정보 PK, 상태정보를 배열로 받음.
		 * @return: int
		 */
		public function updateChargeStatus($param)
		{
			$query = 'UPDATE `imi_mileage_charge` SET
					   `charge_status` = ?,
					   `spare_cost` = `spare_cost` - ?,
					   `use_cost` = `use_cost` + ?
					   WHERE `idx` = ?';
			
			$result = $this->db->execute($query, $param);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return -1;
			}
			return $affected_row;
		}

		/**
		 * @brief: 충전내역의 사용금액이 0원인 항목에 대해 카운트
		 * @param: 없음 
		 * @return: int
		 */
		public function getCountChargeSpareCountZero()
		{
			$query = 'SELECT COUNT(`spare_cost`) spare_cost
					  FROM `imi_mileage_charge`
					  WHERE `spare_cost` < 1
					  AND `charge_status` <> 6
					  FOR UPDATE
					';
			
			$result = $this->db->execute($query);

			if ($result == false) {
				return false;
			}
			
			return $result->fields['spare_cost'];
		}

		/**
		 * @brief: 충전내역의 사용금액이 0원인 항목에 대해 일괄처리 (사용안함: imi_mileage_code.mileage_code = 6)
		 * @param: 충전내역 키 값만 받아서 처리(array)
		 * @return: int
		 */
		public function updateChargeZeroStatus()
		{
			$query = 'UPDATE `imi_mileage_charge` SET
					   `charge_status` = 6
					   WHERE `spare_cost` = 0';
			
			$result = $this->db->execute($query);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}
			return $affected_row;
		}


		/**
		 * @brief: imi_mileage_change에 들어갈 내용 가져오기(FOR UPDATE)
		 * @param: 충전정보 키 값
		 * @return: array
		 */
		public function getChargeInsertData($idx)
		{
			$query = 'SELECT `member_idx`,
							 `mileage_idx`,
							 `charge_account_no` ,
							 `charge_infomation`,
							 `charge_name`,
							 `charge_status`,
							 `idx`,
							 `charge_cost`
					  FROM `imi_mileage_charge`
					  WHERE `idx` = ? 
					  FOR UPDATE';
			
			$result = $this->db->execute($query,$idx);

			if ($result == false) {
				return false;
			}
			
			return $result;
		}

		/**
         * @brief: 모든 회원 마일리지 합계 가져오기
         * @param: NONE
         * @return: array
         */
		public function getAllMemberMileageTotal()
		{
			$today = date('Y-m-d');

			$param = [$today];

			$query = 'SELECT `member_idx`,
							  ifnull(sum(`charge_cost`),0) charge_cost
					  FROM `imi_mileage_charge`
					  WHERE `mileage_idx` <> 5
					  AND `charge_status` = 3
					  AND `expiration_date` < ?
					  GROUP BY `member_idx`
					  ORDER BY `member_idx`
					  FOR UPDATE';
			
			$result = $this->db->execute($query,$param);

			if ($result == false) {
				return false;
			}
			
			return $result;
		}

		/**
         * @brief: 모든 회원 마일리지 유형별 합계 가져오기
         * @param: NONE
         * @return: array
         */
		public function getAllMemberPartMileageTotal()
		{
			$today = date('Y-m-d');

			$param = [$today];

			$query = 'SELECT `member_idx`,
							 `mileage_idx`,
							  ifnull(sum(`charge_cost`),0) charge_cost
					  FROM `imi_mileage_charge`
					  WHERE `mileage_idx` <> 5
					  AND `expiration_date` < ?
					  AND `charge_status` = 3
                      GROUP BY `member_idx`, `mileage_idx`
					  FOR UPDATE';

			$result = $this->db->execute($query,$param);

			if ($result == false) {
				return false;
			}
			
			return $result;
        }
	}
