<?php
	/**
     * 거래 클래스 
     */ 
	Class DealingsClass 
	{
		/** @var string|null $db 는 데이터베이션 커넥션 객체를 할당하기 전에 초기화 함*/
		private $db = null;
        
        /**
		 * 객체 체크 
		 *
		 * @return bool
		 */
		private function checkConnection()
		{
			if(!is_object($this->db)) {
				return false;
			}
			return true;
		}
		
		/**
		 * 데이터베이스 커넥션을 생성하는 함수 
		 *
		 * @param object $db 데이터베이스 커넥션 
		 * 
		 * @return void
		 */
		public function __construct($db) 
		{
			$this->db = $db;
		}
        
        /**
         * 거래 글  폼 데이터 유효성 검증
         *
         * @param array $postData
         *
         * @return array
         */
		public function checkDealingFormValidate($postData)
		{
			if (isset($postData['vourcher_state']) && empty($postData['vourcher_state'])) {
				return ['isValid'=>false, 'errorMessage'=>'처리상태를 입력하세요.'];
			}

			if (isset($postData['dealings_type']) && empty($postData['dealings_type'])) {
				return ['isValid'=>false, 'errorMessage'=>'거래종류를 입력하세요.'];
			}

			if (isset($postData['dealings_subject']) && empty($postData['dealings_subject'])) {
				return ['isValid'=>false, 'errorMessage'=>'제목을 입력하세요'];
			}

			if (isset($postData['dealings_content']) && empty($postData['dealings_content'])) {
				return ['isValid'=>false, 'errorMessage'=>'내용을 입력하세요'];
			}

			if (isset($postData['item_no']) && empty($postData['item_no'])) {
				return ['isValid'=>false, 'errorMessage'=>'구매하고자 하는 상품권을 선택하세요.'];
			}

			if (isset($postData['item_money']) && empty($postData['item_money'])) {
				return ['isValid'=>false, 'errorMessage'=>'상품권 금액을 선택하세요.'];
			}

			if (isset($postData['item_object_no']) && empty($postData['item_object_no'])) {
				return ['isValid'=>false, 'errorMessage'=>'고유번호를 입력하세요'];
			}

			if (isset($postData['dealings_mileage']) && empty($postData['dealings_mileage'])) {
				return ['isValid'=>false, 'errorMessage'=>'거래금액을 입력하세요'];
			}

			return ['isValid'=>true, 'errorMessage'=>''];
		}
        
        /**
         * 거래 가능한 상품권 리스트 출력
         *
         * @param string $is_sell
         * @param $isUsseForUpdate 트랜잭션 사용 시 SELECT문에 FOR UPDATE 설정여부
         *
         * @return array/bool
         */
		public function getVoucherList($isUseForUpdate = false)
		{
			$param = ['Y'];

			$query = 'SELECT `idx`, `item_name`, `commission` 
					  FROM `imi_sell_item` 
					  WHERE `is_sell` = ?';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result;
		}
        
        /**
         * 거래상태 코드 가져오기 
         *
         * @param array $param
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return int/bool
         */
		public function getDealingsStatus($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `idx` FROM `imi_dealings_status_code` WHERE `dealings_status_name` = ?';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result->fields['idx'];
		}
		
        /**
         * 거래 글 생성 
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function insertDealings($param)
		{
			$query = 'INSERT INTO `imi_dealings` SET
						`expiration_date` = ?,
						`register_date` = ?,
						`dealings_type` = ?,
						`dealings_subject` = ?,
						`dealings_content` = ?,
						`item_no` = ?,
						`item_money` = ?,
						`item_object_no` = ?,
						`dealings_mileage` = ?,
						`dealings_commission` = ?,
						`dealings_status` = ?,
						`memo` = ?,
						`writer_idx` = ?';
			
			$result = $this->db->execute($query,$param);
			$inserId = $this->db->insert_id();

			if ($inserId < 1) {
				return false;
			}

			return $inserId;
		}
        
        /**
         * 거래 글 수정 
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function updateDealings($param)
		{
			$query = 'UPDATE `imi_dealings` SET
					   `dealings_subject` = ?,
					   `dealings_content` = ?,
					   `item_no` = ?,
					   `item_money` = ?,
					   `dealings_mileage` = ?,
					   `memo` = ?,
					   `item_object_no` = ?
					   WHERE `idx` = ?';

			$result = $this->db->execute($query,$param);

			$affected_row = $this->db->affected_rows();
			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}
		
        /**
         * 거래 처리과정 생성
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function insertDealingsProcess($param)
		{
			$query = 'INSERT INTO `imi_dealings_process` SET
						`dealings_idx` = ?,
						`dealings_status_idx` = ?,
						`dealings_datetime` = now()';
			
			$result = $this->db->execute($query,$param);
			$inserId = $this->db->insert_id();

			if ($inserId < 1) {
				return false;
			}

			return $inserId;
		}
		
        /**
         * 거래가능한 리스트 가져오기
         *
         * @param array $param
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getDealingsList($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `id`.`idx`,
							 `id`.`register_date`,
							 `id`.`dealings_subject`,
							 `id`.`item_no`,
							 `id`.`item_money`,
							 `id`.`dealings_mileage`,
							 `isi`.`item_name`
					  FROM `imi_dealings` `id`
						INNER JOIN `imi_sell_item` `isi`
							ON `id`.`item_no` = `isi`.`idx`
					  WHERE `id`.`dealings_type` = ?
					  AND `id`.`dealings_status` = ?
					  AND `id`.`is_del` = ?';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result;
		}
        
        /**
         * 거래 상세 데이터 가져오기 
         *
         * @param int $dealingsIdx
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getDealingsData($idx, $isUseForUpdate = false)
		{
			$query = 'SELECT `id`.`idx`,
							 `id`.`dealings_type`,
							 `id`.`register_date`,
							 `id`.`expiration_date`,
							 `id`.`dealings_subject`,
							 `id`.`dealings_content`,
							 `id`.`item_money`,
							 `id`.`item_no`,
							 `id`.`item_object_no`,
							 `id`.`dealings_mileage`,
							 `id`.`dealings_commission`,
							 `id`.`dealings_status`,
							 `id`.`writer_idx`,
							 `id`.`memo`,
							 `im`.`name`,
							 `im`.`id`,
							 `isi`.`item_name`,
							 `idsc`.`dealings_status_name`,
							 `idu`.`dealings_member_idx`
					  FROM `imi_dealings` `id`
						INNER JOIN `imi_members` `im`
							ON `id`.`writer_idx` = `im`.`idx`
						INNER JOIN `imi_sell_item` `isi`
							ON `id`.`item_no` = `isi`.`idx`
						INNER JOIN `imi_dealings_status_code` `idsc`
							ON `id`.`dealings_status` = `idsc`.`idx`
						LEFT JOIN `imi_dealings_user` `idu`
							ON `id`.`idx` = `idu`.`dealings_idx`
					  WHERE `id`.`idx` = ?
					  ORDER BY `register_Date` DESC';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query, $idx);
			if ($result === false) {
				return false;
			}

			return $result;
		}
        
        /**
         * 거래 데이터 유효성 검증 데이터 
         *
         * @param int $dealingsIdx
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getValidDealingsData($idx, $isUseForUpdate = false)
		{
			$query = 'SELECT `id`.`idx`,
							 `id`.`dealings_type`,
							 `id`.`register_date`,
							 `id`.`expiration_date`,
							 `id`.`dealings_subject`,
							 `id`.`dealings_content`,
							 `id`.`item_money`,
							 `id`.`item_no`,
							 `id`.`dealings_mileage`,
							 `id`.`dealings_commission`,
							 `id`.`dealings_status`,
							 `id`.`writer_idx`,
							 `id`.`memo`,
							 `isi`.`item_name`,
							 `idu`.`dealings_member_idx`
					  FROM `imi_dealings` `id`
						INNER JOIN `imi_sell_item` `isi`
							ON `id`.`item_no` = `isi`.`idx`
						LEFT JOIN `imi_dealings_user` `idu`
							ON `id`.`idx` = `idu`.`dealings_idx`
					  WHERE `id`.`idx` = ?';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $idx);
			if ($result === false) {
				return false;
			}

			return $result;
		}
        
        /**
         * 거래데이터가 이미 처리가 되었는지 확인 (이중등록 방지)
         *
         * @param array $param
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return int/bool
         */
		public function isDealingsDataExist($param, $isUseForUpdate = false)
		{
			$query = 'SELECT count(`idx`) cnt 
					  FROM `imi_dealings`
					  WHERE `idx` = ? 
					  AND `is_del` = ?
					  AND `dealings_status` = ?';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result->fields['cnt'];
		}
        
        /**
         * 다음 거래 상태 가져오기 
         *
         * @param array $param
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return int/bool
         */
		public function getNextDealingsStatus($param, $isUseForUpdate = false)
		{
			$query = 'SELECT MIN(idx) next_idx
					  FROM `imi_dealings_status_code`
					  WHERE `idx` > ?';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result->fields['next_idx'];
		}
		
        /**
         * 거래 유저 삽입
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function insertDealingsUser($param)
		{
			$query = 'INSERT INTO `imi_dealings_user` SET
						`dealings_idx` = ?,
						`dealings_writer_idx` = ?,
						`dealings_member_idx` = ?,
						`dealings_status` = ?,
						`dealings_type` = ?,
						`dealings_date` = curdate()';
			
			$result = $this->db->execute($query,$param);
			$inserId = $this->db->insert_id();

			if ($inserId < 1) {
				return false;
			}
			return $inserId;
		}
        
        /**
         * 거래 유저 상태 수정 
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function updateDealingsUser($param)
		{
			$query = 'UPDATE `imi_dealings_user` SET 
					  `dealings_status` = ?,
					  `dealings_date` = curdate()
					  WHERE `dealings_idx` = ?';

			$result = $this->db->execute($query,$param);

			$affected_row = $this->db->affected_rows();
			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}

		/**
         * 거래 상태 수정 
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function updateDealingsStatus($param)
		{
			$query = 'UPDATE `imi_dealings` SET 
					  `dealings_status` = ?
					  WHERE `idx` = ?';

			$result = $this->db->execute($query,$param);
			$affected_row = $this->db->affected_rows();
			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}
        
        /**
         * 일정기간동안 거래가 이루어지지 않을 경우 삭제/ 거래 삭제   (삭제를 다시 복구도 가능)
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function updateDealingsDeleteStatus($param)
		{
			$query = 'UPDATE `imi_dealings` SET 
					  `is_del` = ?,
					  `dealings_status` = ?
					  WHERE `idx` = ?';

			$result = $this->db->execute($query,$param);
			$affected_row = $this->db->affected_rows();
			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}
        
        /**
         * 거래 마일리지 변동내역에 상태도 변경
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function updateDealingsChange($param)
		{
			$query = 'UPDATE `imi_dealings_mileage_change` SET
						`dealings_status_code` = ?
						WHERE `dealings_idx` = ?';
			
			$result = $this->db->execute($query,$param);

			$affected_row = $this->db->affected_rows();
			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}
        
        /**
         * 사용자가 거래한 항목 가져오기 
         *
         * @param array $param
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getDealingIngList($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `idu`.`dealings_idx`,
							 `idu`.`dealings_status`,
							 `idu`.`dealings_date`,
							 `id`.`dealings_subject`,
							 `id`.`item_money`,
							 `id`.`dealings_mileage`,
							 `isi`.`item_name`,
							 `idc`.`dealings_status_name`
					  FROM `imi_dealings_user` `idu`
						INNER JOIN `imi_dealings` `id`
							ON `idu`.`dealings_idx` = `id`.`idx`
						INNER JOIN `imi_sell_item` `isi`
							ON `id`.`item_no` = `isi`.`idx`
						INNER JOIN `imi_dealings_status_code` `idc`
							ON `idu`.`dealings_status` = `idc`.`idx`
					  WHERE `idu`.`dealings_member_idx` = ?
					  AND `idu`.`dealings_type` = ?
					  AND `idu`.`dealings_status` <> ?
					  AND `id`.`is_del` = ?
					  ORDER BY `idu`.`dealings_date` DESC, `id`.`dealings_subject` ASC';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result;
		}
        
        /**
         * 사용자에 의해 결제가 되거나 결제취소 된 내용 가져오기 
		 *
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getPayCompletedDealingIngList($isUseForUpdate = false)
		{
			$query = 'SELECT `idu`.`dealings_idx`,
							 `idu`.`dealings_status`,
							 `idu`.`dealings_date`,
							 `idu`.`dealings_writer_idx`,
							 `idu`.`dealings_member_idx`,
							 `idu`.`dealings_type`,
							 `id`.`dealings_subject`,
							 `id`.`item_money`,
							 `id`.`dealings_mileage`,
							 `isi`.`item_name`,
							 `idc`.`dealings_status_name`,
							 `im`.`name`,
							 `im`.`id`
					  FROM `imi_dealings_user` `idu`
						INNER JOIN `imi_dealings` `id`
							ON `idu`.`dealings_idx` = `id`.`idx`
						INNER JOIN `imi_sell_item` `isi`
							ON `id`.`item_no` = `isi`.`idx`
						INNER JOIN `imi_dealings_status_code` `idc`
							ON `idu`.`dealings_status` = `idc`.`idx`
						INNER JOIN `imi_members` `im`
							ON `id`.`writer_idx` = `im`.`idx`
					  WHERE `idu`.`dealings_status` IN (3,4,5)
					  ORDER BY `idu`.`dealings_date` DESC, `id`.`dealings_subject` ASC';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query);

			if ($result === false) {
				return false;
			}

			return $result;
		}
        
        /**
         * 나의 구매등록현황 가져오기 
         *
         * @param array $param
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getMyDealingList($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `id`.`idx`,
							 `id`.`dealings_status`,
							 `id`.`dealings_subject`,
							 `id`.`item_money`,
							 `id`.`item_object_no`,
							 `id`.`dealings_mileage`,
							 `idu`.`dealings_date`,
							 `id`.`item_no`,
							 `isi`.`item_name`,
							 `idc`.`dealings_status_name`
					  FROM `imi_dealings` `id`
						LEFT JOIN `imi_dealings_user` `idu`
							ON `id`.`idx` = `idu`.`dealings_idx`
						INNER JOIN `imi_sell_item` `isi`
							ON `id`.`item_no` = `isi`.`idx`
						INNER JOIN `imi_dealings_status_code` `idc`
							ON `id`.`dealings_status` = `idc`.`idx`
					  WHERE `id`.`dealings_type` = ?
					  AND `id`.`is_del` = ?
					  AND `id`.`writer_idx` = ?
					  ORDER BY `idu`.`dealings_date` DESC, `id`.`dealings_subject` ASC';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result;
		}
        
        /**
         * 거래 유저 테이블에 값이 있는 지 체크 (이중등록방지)
         *
         * @param int $dealingsIdx
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return int/bool
		 */
		public function isExistDealingsIdx($dealings_idx, $isUseForUpdate = false)
		{
			$query = 'SELECT COUNT(`idx`) cnt 
					  FROM `imi_dealings_user` 
					  WHERE `dealings_idx` = ?';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $dealings_idx);
			if ($result === false) {
				return false;
			}

			return $result->fields['cnt'];
		}
        
        /**
         * 거래 환불/완료 시에 필요한 데이터 추출 
         *
         * @param int $dealingsIdx
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getMileageChargeDataByDealings($dealingsIdx, $isUseForUpdate = false)
		{
			$query = 'SELECT `idu`.`dealings_date`,
							 `id`.`dealings_mileage`,
							 `id`.`dealings_commission`,
							 ROUND((`id`.`dealings_mileage` * `id`.`dealings_commission`)/100) `commission`,
							 `id`.`dealings_subject`,
							 `id`.`dealings_status`,
							 `id`.`item_no`,
							 `id`.`idx`,
							 `idu`.`dealings_member_idx`,
							 `idu`.`dealings_writer_idx`
					  FROM `imi_dealings` `id`
						INNER JOIN `imi_dealings_user` `idu`
							ON `id`.`idx` = `idu`.`dealings_idx`
						INNER JOIN `imi_dealings_status_code` `ids`
							ON `id`.`dealings_status` = `ids`.`idx`
					  WHERE `id`.`idx` = ?';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query, $dealingsIdx);
			if ($result === false) {
				return false;
			}

			return $result;
		}
        
        /**
         * 거래가 등록 시점으로부터 5일 지난 데이터 가져오기 (거래대기/결제대기 인 것만)
         *
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
		 *
         * @return array/bool
         */
		public function getDealingsDeleteList($isUseForUpdate = false)
		{
			$param = [
				'is_del'=>'N',
				'expiration_date'=>date('Y-m-d')
			];

			$query = 'SELECT `idx`,
							 `dealings_status`
					  FROM `imi_dealings`
					  WHERE `is_del` = ?
					  AND `expiration_date` < ?
					  AND `dealings_status` IN (1,2)';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query,$param);
			if ($result === false) {
				return false;
			}
			
			return $result;
		}
        
        /**
         * 거래 중 삭제가 되는 데이터를 배열로 받기 
         *
         * @param array $list
         *
         * @return array/bool
         */
		public function getDealingsDeleteData($list)
		{
			$count = $list->recordCount();
			
			if ($count > 0) {
				foreach($list as $key => $value){
					$delData[] = [
						'dealings_status'=>$value['dealings_status'],
						'idx'=>$value['idx']
					];
				}
			} else {
				return false;
			}
			return $delData;
		}
        
        /**
         * 거래 마일리지 변동정보에 결제 정보가 있는지 확인 (할인 쿠폰 적용시 없음)
         *
         * @param int $dealingsIdx
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getMileageChangeIdx($dealingsIdx, $isUseForUpdate = false)
		{
			$query = 'SELECT `idx` 
					  FROM `imi_dealings_mileage_change` 
					  WHERE `dealings_idx` = ?';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $dealingsIdx);
			if ($result === false) {
				return false;
			}
            
            return $result;
		}
        
        /**
         * 거래 수수료 가져오기
         *
         * @param int $dealingsIdx
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return int/bool
         */
		public function getCommission($dealingsIdx, $isUseForUpdate = false)
		{	
			$query = 'SELECT `dealings_commission` FROM `imi_dealings` WHERE `idx` = ?';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $dealingsIdx);
			if ($result === false) {
				return false;
			}

			return $result->fields['dealings_commission'];
		}
        
        /**
         * 거래 타입 가져오기 
         *
         * @param int $dealingsIdx
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부ㄴ
         *
         * @return string/bool
         */
		public function getDealingsType($dealingsIdx, $isUseForUpdate = false)
		{	
			$query = 'SELECT `dealings_type` FROM `imi_dealings` WHERE `idx` = ?';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $dealingsIdx);
			if ($result === false) {
				return false;
			}

			return $result->fields['dealings_type'];
		}
	}
