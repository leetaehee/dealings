<?php
	/**
     * 거래품목 클래스
     */
	Class SellItemClass 
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
         * 수수료 가져오기
         *
         * @param int $itemIdx
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return int/bool
         */
		public function getCheckSellItemValue($itemIdx, $isUseForUpdate = false)
		{
			$query = 'SELECT `commission` FROM `th_sell_item` WHERE `idx` = ?';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $itemIdx);
			if ($result === false) {
				return false;
			}
			
			return $result->fields['commission'];
		}
        
        /**
         * 판매물품의 항목 가져오기 
         *
         * $param int $itemNo
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return  array/bool
         */
		public function getSellItemData($itemNo, $isUseForUpdate = false)
		{
			$query = 'SELECT * FROM `th_sell_item` WHERE `idx` = ?';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $itemNo);
			if ($result === false) {
				return false;
			}
			
			return $result;
		}
	}