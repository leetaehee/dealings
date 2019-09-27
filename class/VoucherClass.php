<?php
	/**
	 * @file VoucherClass.php
	 * @brief 상품권 클래스, 상품권에 대한 기능 서술
	 * @author 이태희
	 */
	Class VoucherClass 
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
		 * @brief: 상품권 고유번호 가져오기
		 * @param: 상품권 이름 
		 * @return: int
		 */
		public function getVoucherItemIdx($voucherName)
		{
			$param = [
				'item_name'=> $voucherName
			];

			$query = 'SELECT `idx` FROM `imi_sell_item` WHERE `item_name` = ? FOR UPDATE';

			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result->fields['idx'];
		}
	}