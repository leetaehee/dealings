<?php
    /**
	 * @file VirtualAccountClass.php
	 * @brief 가상계좌에 대한 클래스
	 * @author 이태희
	 */
	Class VirtualAccountClass 
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
         * @author: LeeTaeHee
         * @param: 커넥션 파라미터
         */
		public function __construct($db) 
		{
			$this->db = $db;
		}

        /**
         * @brief: 유효성 검증(마일리지)
         * @param: 폼 데이터
         * @return: array
         */
		public function checkFormValidate($postData)
		{
			if (isset($postData['accountBank']) && empty($postData['accountBank'])) {
				return ['isValid'=>false, 'errorMessage'=>'입금은행을 입력하세요'];
			}

			return ['isValid'=>true, 'errorMessage'=>''];
		}

        /**
         * @brief: 가상계좌번호 조회 
         * @param: 은행명, 회원 PK을 가지는 array
         * @return: string
         */
		public function getVirtualAccount($param)
		{			
			$query = 'SELECT `virtual_account_no`
					  FROM `imi_member_virtual_account`
					  WHERE `member_idx` = ?
					  AND `bank_name` = ?
					';
			
			$result = $this->db->execute($query,$param);
			if ($result === false) {
				return false;
			}

			return $result->fields['virtual_account_no'];
		}
        
        /**
         * @brief: 가상계좌번호 추가
         * @param: 은행명, 회원 PK을 가지는 array 
         * @return: int
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
	}