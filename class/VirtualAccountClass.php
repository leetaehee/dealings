<?php
    /** 
	 * 가상 계좌 클래스 
     */
	Class VirtualAccountClass 
	{
		/** @var string|null $db 는 데이터베이션 커넥션 객체를 할당하기 전에 초기화 함*/s
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
         * 가상계좌 생성 시 유효성 검증
         *
         * @param array $postData
         *
         * @return array
         */
		public function checkFormValidate($postData)
		{
			if (isset($postData['accountBank']) && empty($postData['accountBank'])) {
				return ['isValid'=>false, 'errorMessage'=>'입금은행을 입력하세요'];
			}

			return ['isValid'=>true, 'errorMessage'=>''];
		}
        
        /**
         * 가상 계좌 조회
         *
         * @param array $param
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return stirng/bool
         */
		public function getVirtualAccount($param, $isUseForUpdate = false)
		{			
			$query = 'SELECT `virtual_account_no`
					  FROM `imi_member_virtual_account`
					  WHERE `member_idx` = ?
					  AND `bank_name` = ?';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result->fields['virtual_account_no'];
		}
        
        /**
         * 가상 계좌번호 추가
         * 
         * @param array $param
         *
         * @return array/bool
         */
		public function insertVirtualAccount($param)
		{
			// 가상계좌번호 임시생성(오늘날짜 시분초 회원PK)
			$param['account_no'] = setEncrypt(date('YmdHis').''.$param['idx']);
			
			$query = 'INSERT INTO `imi_member_virtual_account` SET
						`member_idx` = ?,
						`bank_name` = ?,
						`virtual_account_no` = ?
					';
			
			$result = $this->db->execute($query, $param);
			$inserId = $this->db->insert_id(); // 추가

			if ($inserId < 1) {
				return false;
			}

			return ['insert_id'=>$inserId, 'account_no'=>$param['account_no']];
		}
        
        /** 
         * 가상 계좌, 은행 가져오기
         * 
         * @param int $memberIdx
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getVirtualAccountData($memberIdx, $isUseForUpdate = false)
		{			
			$query = 'SELECT `virtual_account_no`,
							 `bank_name`
					  FROM `imi_member_virtual_account`
					  WHERE `member_idx` = ?';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query, $memberIdx);
			if ($result === false) {
				return false;
			}

			return $result;
		}
	}