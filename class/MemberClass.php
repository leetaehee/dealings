<?php
	/**
	 * @file MemberClass.php
	 * @brief Login/Logout에 대한 클래스
	 * @author 이태희
	 */
	Class MemberClass 
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
		 * @brief: 유효성 검증(회원가입)
		 * @param: 폼 데이터
		 * @return: true/false, error-message 배열
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
		 * @brief: 유효성 검증(계좌정보)
		 * @param: 폼 데이터
		 * @return: true/false, error-message 배열
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
		 * @brief: 회원 가입/수정 시 중복체크 (FOR UPDATE)
		 * @param: id, email, phone 을 담는 array
		 * @return: int 
		 */
        public function getAccountOverlapCount($accountData)
        { 
            $search = '';
            if (isset($accountData['id'])) {
                $search = 'OR `id` = ?';
            }
            
            $query = "SELECT COUNT(`id`)  cnt 
                      FROM `imi_members`
                      WHERE `phone` = ? OR `email` = ? {$search}
					  FOR UPDATE";
            $result = $this->db->execute($query,$accountData);
            
			if ($result === false) {
				return false;
			}
			
			return $result->fields['cnt'];
        }

		/**
		 * @brief: 회원 가입 시 중복 체크(아이디)
		 * @param: id
		 * @return: true/false
		 */
		public function getIdOverlapCount($id)
		{
			$query = "SELECT count(id) cnt FROM `imi_members` WHERE `id` = ? FOR UPDATE";
			$result = $this->db->execute($query,$id);

			if ($result === false) {
				return false;
			}
			
			return $result->fields['cnt'];
		}

		/**
		 * @brief: 회원 가입 시 중복 체크(휴대폰)
		 * @param: phone
		 * @return: true/false
		 */
		public function getPhoneOverlapCount($phone)
		{
			$phone = removeHyphen($phone);

			$query = "SELECT count(phone) cnt FROM `imi_members` WHERE `phone` = ? FOR UPDATE";
			$result = $this->db->execute($query, setEncrypt($phone));

			if ($result === false) {
				return false;
			}
			return $result->fields['cnt'];
		}
		
		/**
		 * @brief: 회원 가입 시 중복 체크(이메일)
		 * @param: email
		 * @return: true/false
		 */
		public function getEmailOverlapCount($email)
		{	
			$query = "SELECT count(phone) cnt FROM `imi_members` WHERE `email` = ? FOR UPDATE";
			$result = $this->db->execute($query, setEncrypt($email));

			if ($result === false) {
				return false;
			}
			return $result->fields['cnt'];
		}
		
		/**
		 * @brief: 회원 가입 저장
		 * @param: form 데이터
		 * @return: true/false
		 */
		public function insertMember($param)
		{
			$query = "INSERT INTO `imi_members` SET
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
		 * @brief: 회원 수정
		 * @param: form 데이터
		 * @return: int 
		 */
		public function updateMember($param)
		{
			$query = 'UPDATE `imi_members` SET
					   `password` = ?,
					   `email` = ?,
					   `phone` = ?,
					   `name` = ?,
					   `sex` = ?,
					   `birth` = ?,
					   `modify_date` = CURDATE()
					  WHERE idx = ?
					';
			$result = $this->db->execute($query,$param);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}

		/**
		 * @brief: 가입승인메일 날짜 체크
		 * @param:  회원 고유 키
		 * @return: true/false
		 */
		public function getJoinApprovalMailDate($idx)
		{
			$query = 'SELECT `join_approval_date` FROM `imi_members` WHERE `idx` = ? FOR UPDATE';
			$result = $this->db->execute($query, $idx);

			if ($result === false) {
				return false;
			}
			return $result->fields['join_approval_date'];
		}

		/**
		 * @brief: 가입승인메일 오늘날짜로 변경
		 * @param:  회원 고유 키 
		 * @return: true/false
		 */
		public function updateJoinApprovalMailDate($idx)
		{
			$query = 'UPDATE `imi_members` SET `join_approval_date` = CURDATE() WHERE `idx` = ?'; 
			$result = $this->db->execute($query,$idx);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}
        
		/**
		 * @brief: 회원정보 출력
		 * @param:  회원 고유 키
		 * @return: array
		 */
        public function getMyInfomation($idx)
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
                      FROM `imi_members` `imi`
						INNER JOIN `imi_member_grades` `img` 
							ON `imi`.`grade_code` = `img`.`grade_code`
                      WHERE `idx` = ?
					  FOR UPDATE';
            $result = $this->db->execute($query, $param);
            
			if ($result == false) {
				return false;
			}
            
			return $result;
        }
        
		/**
		 * @brief: 회원탈퇴 처리 (update)
		 * @author: LeeTaeHee
		 * @param:  회원 고유 키
		 * @return: boolean
		 */
        public function deleteMember($idx)
        { 
            $query = 'UPDATE `imi_members` 
                      SET `withdraw_date` = CURDATE(),
					      `modify_date` = CURDATE()
				      WHERE `idx` = ?';
		    
            $result = $this->db->execute($query,$idx);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
        }
		
		/**
		 * @brief: 회원 계좌정보 가져오기
		 * @param:  회원 고유 키
		 * @return: object
		 */
		public function getAccountByMember($idx)
		{
			$query = 'SELECT `idx`,
							 `account_no`,
							 `account_bank`
					  FROM `imi_members`
					  WHERE `idx` = ?
					  FOR UPDATE
					';

			$result = $this->db->execute($query,$idx);

			if ($result === false) {
				return false;
			}
            
			return $result;
		}

		/**
		 * @brief: 회원 계좌정보 수정하기
		 * @param:  회원 고유 키, 계좌 내용
		 * @return: array
		 */
		public function updateMyAccount($param)
		{
			$query = 'UPDATE `imi_members` SET
					   `account_no` = ?,
					   `account_bank` = ?
					  WHERE `idx` = ?
					';
			$result = $this->db->execute($query,$param);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}

        /**
         * @brief: 회원테이블 마일리지 변경 (충전시)
         * @param: 회원 PK, 변경 마일리지 금액
         * @return: int 
         */
		public function updateMileageCharge($param)
		{
			$query = 'UPDATE `imi_members` SET
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
         * @brief: 회원테이블 마일리지 변경 (출금시)
         * @param: 회원 PK, 변경 마일리지 금액
         * @return: int 
         */
		public function updateMileageWithdrawal($param)
		{
			$query = 'UPDATE `imi_members` SET
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
         * @brief: 유효기간 지난 마일리지로 인한 대량 변동 시 사용 (마일리지 차감)
				   - 모든 회원에 대해 마일리지 수정 (크론탭사용)
         * @param: 회원 PK, 변경 마일리지 금액
         * @return: int 
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

					$query = 'UPDATE `imi_members` SET
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
         * @brief: [관리자 메뉴] 회원 현황 리스트
         * @param: 없음
         * @return: array 
         */
		public function getMemberList()
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
					  FROM `imi_members`
					  WHERE `forcedEviction_date` IS NULL
					  AND `withdraw_date` IS NULL
					  AND `join_approval_date` IS NOT NULL
					  FOR UPDATE';

			$result = $this->db->execute($query);

			if ($result === false) {
				return false;
			}
            
			return $result;
		}

		/**
         * @brief: [관리자 메뉴] 현재 활동 중인 회원 리스트 
         * @param: 없음
         * @return: array 
         */
		public function getActivityMemberList()
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
					  FROM `imi_members`
					  WHERE `forcedEviction_date` IS NULL
					  AND `withdraw_date` IS NULL
					  AND `join_approval_date` IS NOT NULL';

			$result = $this->db->execute($query);

			if ($result === false) {
				return false;
			}
            
			return $result;
		}
	
		/**
         * @brief: 회원 전체 마일리지 가져오기 
         * @param: 회원 키 값
         * @return: int
         */
		public function getTotalMileage($idx)
		{
			$query = 'SELECT `mileage` FROM `imi_members` WHERE `idx` = ? FOR UPDATE';
			$result = $this->db->execute($query, $idx);

			if ($result === false) {
				return false;
			}
            
			return $result->fields['mileage'];

		}
	}