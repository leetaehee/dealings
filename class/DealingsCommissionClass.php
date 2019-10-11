<?php
	/**
     *  수수료 클래스 
     */
	Class DealingsCommissionClass 
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
         * 수수료를 냈는지 체크 (이중등록 방지)
         *
         * @param int $dealingsIdx
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return int/bool
         */
		public function isExistDealingsNo($dealingsIdx, $isUseForUpdate = false)
		{
			$query = 'SELECT `dealings_idx` FROM `imi_dealings_commission` WHERE `dealings_idx` = ?';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $dealingsIdx);
			if ($result === false) {
				return false;
			}
			return $result->fields['dealings_idx'];
		}
        
        /**
         * 수수료 데이터 삽입
         *
         * @param array $param
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return int/bool
         */
		public function insertDealingsCommission($param)
		{
			$query = 'INSERT INTO `imi_dealings_commission` SET
						`dealings_idx` = ?,
						`commission` = ?,
						`dealings_complete_date` = CURDATE(),
						`sell_item_idx` = ?';
			
			$result = $this->db->execute($query, $param);
			$inserId = $this->db->insert_id();

			if ($inserId < 1) {
				return false;
			}

			return $inserId;
		}
	}