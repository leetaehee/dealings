<?php
	/**
	 * @file DealingsCommissionClass.php
	 * @brief 거래 수수료 클래스, 거래시 수수료 관련된 기능 서술
	 * @author 이태희
	 */
	Class DealingsCommissionClass 
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
		 * @brief: 한 거래당 수수료는 한번만 받을 수 있음 
		 * @param: 거래테이블 키
		 * @return: string
		 */
		public function isExistDealingsNo($dealingsIdx)
		{
			$query = 'SELECT `dealings_idx` FROM `imi_dealings_commission` WHERE `dealings_idx` = ? FOR UPDATE';

			$result = $this->db->execute($query, $dealingsIdx);
			if ($result === false) {
				return false;
			}
			return $result->fields['dealings_idx'];
		}

		/**
		 * @brief: 수수료 데이터 생성 
		 * @param: 거래테이블 키와 수수료 정보를 포함한 array
		 * @return: int
		 */
		public function insertDealingsCommission($param)
		{
			$query = 'INSERT INTO `imi_dealings_commission` SET
						`dealings_idx` = ?,
						`commission` = ?,
						`dealings_complete_date` = CURDATE(),
						`sell_item_idx` = ?';
			
			$result = $this->db->execute($query,$param);
			$inserId = $this->db->insert_id();

			if ($inserId < 1) {
				return false;
			}

			return $inserId;
		}
	}