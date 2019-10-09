<?php
	/**
	 * @file SellItemClass.php
	 * @brief 상품권 정보에 대한 설명 기술
	 * @author 이태희
	 */
	Class SellItemClass 
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
		 * @brief: 상품권 고유번호 확인 
		 * @param: 폼 데이터
		 * @return: boolean
		 */
		public function getCheckSellItemValue($itemIdx)
		{
			$query = 'SELECT `commission` FROM `imi_sell_item` WHERE `idx` = ?';

			$result = $this->db->execute($query,$itemIdx);
			if ($result === false) {
				return false;
			}
			
			return $result->fields['commission'];
		}

		/**
		 * @brief: 판매물품 항목 가져오기 
		 * @param: 판매물품의 고유 키
		 * @return: array
		 */
		public function getSellItemData($itemNo)
		{
			$query = 'SELECT * FROM `imi_sell_item` WHERE `idx` = ?';

			$result = $this->db->execute($query,$itemNo);
			if ($result === false) {
				return false;
			}
			
			return $result;
		}
	}