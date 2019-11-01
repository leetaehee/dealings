<?php
	/**
     * 회원클래스
     */
	Class MemberClass 
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
		* 회원 회원가입 시 유효성 검증
		*
		* @param array $postata 회원가입 데이터
		*
		* @return array
		*/
		public function checkMemberFormValidate($postData)
		{
			$repEmail = "/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i";
			$repBirth = "/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/i";
			
			if (isset($postData['id']) && empty($postData['id'])) {
				return ['isValid'=>false, 'errorMessage'=>'아이디를 입력하세요.'];
			}

			if (isset($postData['password']) && isset($postData['repassword'])) {
				
				if (empty($postData['password']) && empty($postData['repassword'])) {
					return ['isValid'=>false, 'errorMessage'=>'패스워드를 입력하세요.'];
				}

				if ($postData['password'] != $postData['repassword']) {
					return ['isValid'=>false, 'errorMessage'=>'패스워드가 서로 일치하지 않습니다.'];
				}
			}

			if (isset($postData['name']) && empty($postData['name'])) {
				return ['isValid'=>false, 'errorMessage'=>'이름을 입력하세요.'];
			}

			if (isset($postData['email']) && empty($postData['email'])) {
				return ['isValid'=>false, 'errorMessage'=>'이메일을 입력하세요.'];
			}else{
				if (preg_match($repEmail,$postData['email'])==false) {
					return ['isValid'=>false, 'errorMessage'=>'이메일을 형식을 확인하세요.'];
				}
			}

			if (isset($postData['birth']) && empty($postData['birth'])) {
				return ['isValid'=>false, 'errorMessage'=>'생년월일을 입력하세요'];
			} else {
				if (preg_match($repBirth, $postData['birth'])==false) {
					return ['isValid'=>false, 'errorMessage'=>'생년월일 날짜 형식 확인하세요!.'];
				}
			}

			if (isset($postData['phone']) && empty($postData['phone'])) {
				return ['isValid'=>false, 'errorMessage'=>'핸드폰번호를 입력하세요'];
			}


			if (isset($postData['sex']) && empty($postData['sex'])) {
				return ['isValid'=>false, 'errorMessage'=>'성별을 입력하세요'];
			}

			return ['isValid'=>true, 'errorMessage'=>''];
		}
        
        /**
         * 계좌정보 유효성 검증 
         *
         * @param array $postData
         *
         * @return array
         */
		public function checkAccountFormValidate($postData)
		{
			if (isset($postData['account_bank']) && empty($postData['account_bank'])) {
				return ['isValid'=>false, 'errorMessage'=>'은행을 입력하세요'];
			}

			if (isset($postData['account_no']) && empty($postData['account_no'])) {
				return ['isValid'=>false, 'errorMessage'=>'은행을 입력하세요'];
			}

			return ['isValid'=>true, 'errorMessage'=>''];
		}
        
        /**
         * 회원 가입/수정 시 중복 체크 
         *
         * @param array $accountData
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         * 
         * @return int/bool
         */
        public function getAccountOverlapCount($accountData, $isUseForUpdate = false)
        { 
            $search = '';
            if (isset($accountData['id'])) {
                $search = 'OR `id` = ?';
            }
            
            $query = "SELECT COUNT(`id`)  cnt 
                      FROM `th_members`
                      WHERE `phone` = ? OR `email` = ? {$search}";
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

            $result = $this->db->execute($query, $accountData);
			if ($result === false) {
				return false;
			}
			
			return $result->fields['cnt'];
        }

		/**
		 * id 중복검사
		 *
		 * @param string $id 아이디 
		 *
		 * @return int/bool
		 */
		public function getIdOverlapCount($id)
		{
			$query = "SELECT count(id) cnt FROM `th_members` WHERE `id` = ?";

			$result = $this->db->execute($query,$id);
			if ($result === false) {
				return false;
			}
			
			return $result->fields['cnt'];
		}

		/**
		 * 핸드폰 중복검사
		 *
		 * @param string $phone 핸드폰 
		 *
		 * @return int/bool
		 */
		public function getPhoneOverlapCount($phone)
		{
			$phone = removeHyphen($phone);
		
			$query = "SELECT count(phone) cnt FROM `th_members` WHERE `phone` = ?";
			
			$result = $this->db->execute($query, setEncrypt($phone));
			if ($result === false) {
				return false;
			}

			return $result->fields['cnt'];
		}
		
		/**
		 * 이메일 중복검사
		 *
		 * @param string $email
	     *
		 * @return int/bool
		 */
		public function getEmailOverlapCount($email)
		{
			$query = "SELECT count(phone) cnt FROM `th_members` WHERE `email` = ?";
			
			$result = $this->db->execute($query, setEncrypt($email));
			if ($result === false) {
				return false;
			}

			return $result->fields['cnt'];
		}
        
        /**
         * 회원 가입 
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function insertMember($param)
		{
			$query = "INSERT INTO `th_members` SET
						`id` = ?,
						`grade_code` = 3,
						`password` = ?,
						`email` = ?,
						`name` = ?,
						`phone` = ?,
						`sex` = ?,
						`birth` = ?,
						`join_date` = CURDATE()
					 ";

			$result = $this->db->execute($query, $param);
			$insertId = $this->db->insert_id(); // 추가

			if ($insertId < 1) {
				return false;
			}

			return $insertId;
		}
        
        /**
         * 회원 수정
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function updateMember($param)
		{
			$query = 'UPDATE `th_members` SET
					   `password` = ?,
					   `email` = ?,
					   `phone` = ?,
					   `name` = ?,
					   `sex` = ?,
					   `birth` = ?,
					   `modify_date` = CURDATE()
					  WHERE idx = ?
					';
			$result = $this->db->execute($query, $param);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}
        
        /**
         * 가입 메일 승인 체크 
         *
         * @param int $idx
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return date/bool
         */
		public function getJoinApprovalMailDate($idx, $isUseForUpdate = false)
		{
			$query = 'SELECT `join_approval_date` 
					  FROM `th_members` 
					  WHERE `idx` = ?';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $idx);

			if ($result === false) {
				return false;
			}
			return $result->fields['join_approval_date'];
		}
        
        /**
         * 가입 메일 승인 일자 수정 
         * 
         * @param int $idx
         *
         * @return int/bool
         */
		public function updateJoinApprovalMailDate($idx)
		{
			$query = 'UPDATE `th_members` 
					   SET `join_approval_date` = CURDATE() 
					   WHERE `idx` = ?';
					   
			$result = $this->db->execute($query, $idx);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}
        
        /**
         * 회원 정보 출력 
         *
         * @param int $idx
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
        public function getMyInfomation($idx, $isUseForUpdate = false)
		{
            $param = ['M',$idx];
            
            $query = 'SELECT `imi`.`idx`,
                             `imi`.`id`,
                             `imi`.`name`,
                             `imi`.`email`,
                             `imi`.`phone`,
							 `imi`.`mileage`,
                             CASE WHEN `imi`.`sex` = ? then "남성" else "여성" end sex_name,
                             `imi`.`sex`,
                             `imi`.`birth`,
                             `imi`.`join_date`,
                             `imi`.`join_approval_date`,
							 `imi`.`mileage`,
							 `imi`.`account_no`,
							 `imi`.`account_bank`,
							 `img`.`grade_name`
                      FROM `th_members` `imi`
						INNER JOIN `th_member_grades` `img` 
							ON `imi`.`grade_code` = `img`.`grade_code`
                      WHERE `idx` = ?';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

            $result = $this->db->execute($query, $param);
            
			if ($result == false) {
				return false;
			}
            
			return $result;
        }
        
        /**
         * 회원 탈퇴
         *
         * @param int $idx
         *
         * @return int/bool
         */
        public function deleteMember($idx)
        { 
            $query = 'UPDATE `th_members` 
                      SET `withdraw_date` = CURDATE(),
					      `modify_date` = CURDATE()
				      WHERE `idx` = ?';
		    
            $result = $this->db->execute($query, $idx);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
        }
        
        /**
         * 회원 계좌정보 가져오기
         *
         * @param int $idx
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         * 
         * @return array/bool
         */
		public function getAccountByMember($idx, $isUseForUpdate = false)
		{
			$query = 'SELECT `idx`,
							 `account_no`,
							 `account_bank`
					  FROM `th_members`
					  WHERE `idx` = ?';

			$result = $this->db->execute($query, $idx);

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			if ($result === false) {
				return false;
			}
            
			return $result;
		}
        
        /**
         * 회원 계좌정보 수정 
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function updateMyAccount($param)
		{
			$query = 'UPDATE `th_members` SET
					   `account_no` = ?,
					   `account_bank` = ?
					  WHERE `idx` = ?
					';
			$result = $this->db->execute($query, $param);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}
        
        /**
         * 회원 마일리지 수정 (증가)
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function updateMileageCharge($param)
		{
			$query = 'UPDATE `th_members` SET
					   `mileage` = `mileage` + ? 
					   WHERE `idx` = ?';

			$result = $this->db->execute($query, $param);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}
        
         /**
         * 회원 마일리지 수정 (감소)
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function updateMileageWithdrawal($param)
		{
			$query = 'UPDATE `th_members` SET
					   `mileage` = `mileage` - ? 
					   WHERE `idx` = ?';

			$result = $this->db->execute($query, $param);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}
        
        /**
         * 유효기간이 지난 마일리지 대량(모든회원) 변동시 사용 (차감)
         *
         * @param array $params
         *
         * @return int/bool
         */
		public function updateAllMemberMilege($params)
		{
			$count = $params->recordCount();
			if ($count > 0) {
				foreach ($params as $key => $value) {
					$param = [
							$value['charge_cost'],
							$value['member_idx']
						];

					$query = 'UPDATE `th_members` SET
							  `mileage` = `mileage` - ? 
							   WHERE `idx` = ?';
					
					$result = $this->db->execute($query, $param);
					$affected_row = $this->db->affected_rows();

					if ($affected_row < 1) {
						return false;
					}
				}
			} else {
				return false;
			}
			return $affected_row;
		}
        
        /**
         * 회원현황 리스트
         *
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부 
		 *
         * @return array/bool
         */
		public function getMemberList($isUseForUpdate = false)
		{
			$query = 'SELECT `idx`,
							 `id`,
							 `name`,
							 `email`,
							 `phone`,
							 `sex`,
							 `join_approval_date`,
							 `mileage`,
							 CASE WHEN `sex` = "M" then "남성" else "여성" end sex_name
					  FROM `th_members`
					  WHERE `forcedEviction_date` IS NULL
					  AND `withdraw_date` IS NULL
					  AND `join_approval_date` IS NOT NULL';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query);

			if ($result === false) {
				return false;
			}
            
			return $result;
		}
        
        /**
         * 강제탈퇴 당하지 않은 회원 리스트 출력
		 *
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getActivityMemberList($isUseForUpdate = false)
		{
			$query = 'SELECT `idx`,
							 `id`,
							 `name`,
							 `email`,
							 `phone`,
							 `sex`,
							 `join_approval_date`,
							 `mileage`,
							 CASE WHEN `sex` = "M" then "남성" else "여성" end sex_name
					  FROM `th_members`
					  WHERE `forcedEviction_date` IS NULL
					  AND `withdraw_date` IS NULL
					  AND `join_approval_date` IS NOT NULL';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query);

			if ($result === false) {
				return false;
			}
            
			return $result;
		}
        
        /**
         * 현재 회원의 마일리지 가져오기
         *
         * @param int $idx
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getTotalMileage($idx, $isUseForUpdate = false)
		{
			$query = 'SELECT `mileage` FROM `th_members` WHERE `idx` = ?';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $idx);

			if ($result === false) {
				return false;
			}
            
			return $result->fields['mileage'];

		}
	}