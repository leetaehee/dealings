<?php
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
		
		public function __construct($db) 
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 커넥션 파라미터
			 * @brief: 데이터베이스 커넥션 생성 
			 */
			$this->db = $db;
		}

		public function checkFormValidate($postData)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 폼 데이터
			 * @brief: 유효성 검증(마일리지)
			 * @return: true/false, error-message 배열
			 */
			
			if (isset($postData['accountBank']) && empty($postData['accountBank'])) {
				return ['isValid'=>false, 'errorMessage'=>'입금은행을 입력하세요'];
			}

			return ['isValid'=>true, 'errorMessage'=>''];
		}

		public function getVirtualAccount($param)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 은행명, 회원 PK을 가지는 배열
			 * @brief: 가상계좌번호 조회 
			 * @return: string
			 */
			
			$query = 'SELECT `virtual_account_no`
					  FROM `imi_member_virtual_account`
					  WHERE `member_idx` = ?
					  AND `bank_name` = ?
					';
			
			$result = $this->db->execute($query,$param);

			if ($result == false) {
				return false;
			}

			return $result->fields['virtual_account_no'];
		}

		public function insertVirtualAccount($param)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 은행명, 회원 PK을 가지는 배열 
			 * @brief: 가상계좌번호 추가
			 * @return: int
			 */

			// 가상계좌번호 임시생성
			$param['account_no'] = setEncrypt(date('Y-m-d-H-i-s').'-'.$param['idx']);
			
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