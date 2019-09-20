<?php
	/**
	 * @file DealingsClass.php
	 * @brief 거래 클래스, 거래와 관련된 기능 서술
	 * @author 이태희
	 */
	Class DealingsClass 
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
		 * @brief: 데이터베이스 커넥션 생성
		 * @param: 커넥션 파라미터
		 */
		public function __construct($db) 
		{
			$this->db = $db;
		}

		/**
		 * @brief: 유효성 검증(거래폼)
		 * @param: 폼 데이터
		 * @return: true/false, error-message 배열
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

			if (isset($postData['commission']) && empty($postData['commission'])) {
				return ['isValid'=>false, 'errorMessage'=>'수수료를 확인하세요'];
			}

			return ['isValid'=>true, 'errorMessage'=>''];
		}

		/**
		 * @brief: 거래 가능한 상품권 리스트 가져오기
		 * @param: none
		 * @return: array 
		 */
		public function getVoucherList()
		{
			$param = ['Y'];

			$query = 'SELECT `idx`, `item_name`, `commission` 
					  FROM `imi_sell_item` 
					  WHERE `is_sell` = ?';

			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result;
		}
		
		/**
		 * @brief: 거래상태 코드 가져오기(transaction)
		 * @param: 거래상태코드
		 * @return: int 
		 */
		public function getDealingsStatus($param)
		{
			$query = 'SELECT `idx` FROM `imi_dealings_status_code` WHERE `dealings_status_name` = ? FOR UPDATE';

			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result->fields['idx'];
		}
		
		/**
		 * @brief: 거래데이터 생성 
		 * @param: 폼 데이터
		 * @return: int 
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
		 * @brief: 거래데이터 수정 
		 * @param: 폼 데이터
		 * @return: int 
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
		 * @brief: 거래 처리과정 데이터 생성  
		 * @param: 폼 데이터
		 * @return: int 
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
		 * @brief: 거래 가능 리스트 가져오기
		 * @param: 조회 구분 
		 * @return: array 
		 */
		public function getDealingsList($param)
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
			
			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result;
		}
		
		/**
		 * @brief: 거래 상세 데이터 가져오기
		 * @param: 거래 테이블 키 값
		 * @return: array 
		 */
		public function getDealingsData($idx)
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
			
			$result = $this->db->execute($query, $idx);
			if ($result === false) {
				return false;
			}

			return $result;
		}

		/**
		 * @brief: 거래 데이터를 구매하기 전에 등록이 됬는지 확인
		 * @param: 거래데이터 키 값
		 * @return: boolean
		 */
		public function isDealingsDataExist($param)
		{
			$query = 'SELECT count(`idx`) cnt 
					  FROM `imi_dealings`
					  WHERE `idx` = ? 
					  AND `is_del` = ?
					  AND `dealings_status` = ?
					  FOR UPDATE';

			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result->fields['cnt'];
		}

		/**
		 * @brief: 다음 거래 상태 가져오기
		 * @param: 거래상태코드 키, 현재 구분값을 담은 array
		 * @return: int
		 */
		public function getNextDealingsStatus($param)
		{
			$query = 'SELECT MIN(idx) next_idx
					  FROM `imi_dealings_status_code`
					  WHERE `idx` > ?
					  FOR UPDATE';

			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result->fields['next_idx'];
		}
		
		/**
		 * @brief: 거래 유저 삽입
		 * @param: 폼 데이터 (거래대상자) array
		 * @return: int
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
		 * @brief: 거래 유저 수정
		 * @param: 폼 데이터 (거래대상자) array
		 * @return: int
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
		 * @brief: 거래 상태정보 수정
		 * @param: 폼 데이터 (거래대상자) array
		 * @return: int
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
		 * @brief: 일정 기간동안 거래가 이루어지지 않을 경우 삭제처리 (삭제를 해제도 가능) 
		 * @param: 폼 데이터 (거래대상자) array
		 * @return: int
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
		 * @brief: 거래 마일리지 변동 테이블의 거래상태 변경 
		 * @param: 거래키와 상태 변경정보를 담은 array
		 * @return: int
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
		 * @brief: 사용자가 구매한 리스트 가져오기
		 * @param: 거래 키 값, 사용자 키 값, 상태코드를 담은 array
		 * @return: array
		 */
		public function getDealingIngList($param)
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
					  ORDER BY `idu`.`dealings_date` DESC';
			
			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result;
		}

		/**
		 * @brief: [관리자] 사용자에 의해 결제가 되거나 결제 취소 된 데이터 가져오기
		 * @param: 거래 키 값, 사용자 키 값, 상태코드를 담은 array
		 * @return: array
		 */
		public function getPayCompletedDealingIngList()
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
			
			$result = $this->db->execute($query);

			if ($result === false) {
				return false;
			}

			return $result;
		}

		/**
		 * @brief: 나의 구매등록현황 모두 가져오기
		 * @param: 삭제 여부, 거래타입, 아이디을 담는 array
		 * @return: array
		 */
		public function getMyDealingList($param)
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
					  ORDER BY `idu`.`dealings_date` DESC';
			
			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result;
		}
		
		/**
		 * @brief: 거래 유저 테이블에 값이 있는지 체크 
		 * @param: 거래테이블 키
		 * @return: int
		 */
		public function isExistDealingsIdx($dealings_idx)
		{
			$query = 'SELECT COUNT(`idx`) cnt 
					  FROM `imi_dealings_user` 
					  WHERE `dealings_idx` = ?
					  FOR UPDATE';

			$result = $this->db->execute($query, $dealings_idx);
			if ($result === false) {
				return false;
			}

			return $result->fields['cnt'];
		}

		/**
		 * @brief: 거래 환불/완료시 imi_mileage_charge 테이블에 필요한 데이터 추출
		 * @param: 거래테이블 키 
		 * @return: array
		 */
		public function getMileageChargeDataByDealings($dealingsIdx)
		{
			$query = 'SELECT `idu`.`dealings_date`,
							 `id`.`dealings_mileage`,
							 `id`.`dealings_commission`,
							 ROUND((`id`.`dealings_mileage` * `id`.`dealings_commission`)/100) `commission`,
							 `id`.`dealings_subject`,
							 `id`.`dealings_status`,
							 `id`.`item_no`
					  FROM `imi_dealings` `id`
						INNER JOIN `imi_dealings_user` `idu`
							ON `id`.`idx` = `idu`.`dealings_idx`
						INNER JOIN `imi_dealings_status_code` `ids`
							ON `id`.`dealings_status` = `ids`.`idx`
					  WHERE `id`.`idx` = ?
					  FOR UPDATE';
			
			$result = $this->db->execute($query, $dealingsIdx);
			if ($result === false) {
				return false;
			}

			return $result;
		}
			
		/**
		 * @brief: 거래(판매/구매)글의 등록시점이 5일이 지난 데이터 가져오기
		 * @param: none 
		 * @return: array
		 */
		public function getDealingsDeleteList()
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
			
			$result = $this->db->execute($query,$param);
			if ($result === false) {
				return false;
			}
			
			return $result;
		}

		/**
		 * @brief: 거래(판매/구매)글 중 삭제되는 데이터를 배열로 받기
		 * @param: 거래 키값과 상태정보를 담은 array
		 * @return: array
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
	}