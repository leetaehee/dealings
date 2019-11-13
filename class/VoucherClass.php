<?php
	/**
	 * 상품권 클래스
	 */
	Class VoucherClass 
	{
		/** @var string|null $db 는 데이터베이션 커넥션 객체를 할당하기 전에 초기화 함*/
		private $db = null;
		
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
         * 상품권 고유번호 가져오기
         *
         * @param string $voucherName
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return int/bool
         */
		public function getVoucherItemIdx($voucherName, $isUseForUpdate = false)
		{
			$param = [
				'item_name'=> $voucherName
			];

			$query = 'SELECT `idx` FROM `th_sell_item` WHERE `item_name` = ?';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result->fields['idx'];
		}
	}