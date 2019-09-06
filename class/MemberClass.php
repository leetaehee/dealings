<?php
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
		
		public function __construct($db) 
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 커넥션 파라미터
			 * @brief: 데이터베이스 커넥션 생성 
			 */
			$this->db = $db;
		}

		public function checkMemberFormValidate($postData)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 폼 데이터
			 * @brief: 유효성 검증(회원가입)
			 * @return: true/false, error-message 배열
			 */

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

		public function checkAccountFormValidate($postData)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 폼 데이터
			 * @brief: 유효성 검증(계좌정보)
			 * @return: true/false, error-message 배열
			 */

			if (isset($postData['account_bank']) && empty($postData['account_bank'])) {
				return ['isValid'=>false, 'errorMessage'=>'은행을 입력하세요'];
			}

			if (isset($postData['account_no']) && empty($postData['account_no'])) {
				return ['isValid'=>false, 'errorMessage'=>'은행을 입력하세요'];
			}

			return ['isValid'=>true, 'errorMessage'=>''];
		}

		public function getIdOverlapCount($id)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: id, db객체
			 * @brief: 회원 가입 시 중복 체크(아이디)
			 * @return: true/false
			 */
			$query = "SELECT count(id) cnt FROM `imi_members` WHERE `id` = ?";
			$result = $this->db->execute($query,$id);

			if ($result == false) {
				return false;
			}
			
			return $result->fields['cnt'];
		}

		public function getPhoneOverlapCount($phone)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: phone, db객체
			 * @brief: 회원 가입 시 중복 체크(휴대폰)
			 * @return: true/false
			 */
			
			$phone = removeHyphen($phone);

			$query = "SELECT count(phone) cnt FROM `imi_members` WHERE `phone` = ?";
			$result = $this->db->execute($query, setEncrypt($phone));

			if ($result == false) {
				return false;
			}
			return $result->fields['cnt'];
		}

		public function getEmailOverlapCount($email)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: email, db객체
			 * @brief: 회원 가입 시 중복 체크(이메일)
			 * @return: true/false
			 */
			
			$query = "SELECT count(phone) cnt FROM `imi_members` WHERE `email` = ?";
			$result = $this->db->execute($query, setEncrypt($email));

			if ($result == false) {
				return false;
			}
			return $result->fields['cnt'];
		}

		public function insertMember($postData)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: form 데이터
			 * @brief: 회원 가입 저장
			 * @return: true/false
			 */

			/* 
			 * 코드값 참조
			 * grade_code = 3 (신규 가입시 부여되는 가장 낮은 레벨)
			 */

			$bindValue = [$postData['id'],
						  password_hash($postData['password'], PASSWORD_DEFAULT),
						  setEncrypt($postData['email']),
						  setEncrypt($postData['name']),
						  setEncrypt($postData['phone']),
						  $postData['sex'],
						  setEncrypt($postData['birth'])
						];

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

			$result = $this->db->execute($query,$bindValue);
			$insertId = $this->db->insert_id(); // 추가

			if ($insertId < 1) {
				return false;
			}

			return $insertId;
		}

		public function updateMember($postData)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: form 데이터
			 * @brief: 회원 수정
			 * @return: int 
			 */

			$param = [
				password_hash($postData['password'], PASSWORD_DEFAULT),
				setEncrypt($postData['email']),
				setEncrypt($postData['phone']),
				setEncrypt($postData['name']),
				$postData['sex'],
				setEncrypt($postData['birth']),
				$postData['idx']
			];

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

		public function getJoinApprovalMailDate($idx)
		{
			/**
			 * @author: LeeTaeHee
			 * @param:  회원 고유 키
			 * @brief: 가입승인메일 날짜 체크
			 * @return: true/false
			 */
	
			$query = 'SELECT `join_approval_date` FROM `imi_members` WHERE `idx` = ? FOR UPDATE';
			$result = $this->db->execute($query, $idx);

			if ($result == false) {
				return false;
			}
			return $result->fields['join_approval_date'];
		}

		public function updateJoinApprovalMailDate($idx)
		{
			/**
			 * @author: LeeTaeHee
			 * @param:  회원 고유 키
			 * @brief: 가입승인메일 오늘날짜로 변경 
			 * @return: true/false
			 */

			$query = 'UPDATE `imi_members` SET `join_approval_date` = CURDATE() WHERE `idx` = ?'; 
			$result = $this->db->execute($query,$idx);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}
        
        public function getMyInfomation($idx)
		{
            /**
			 * @author: LeeTaeHee
			 * @param:  회원 고유 키
			 * @brief: 회원정보 출력
			 * @return: array
			 */
            
            $param = ['M',$idx];
            
            $query = 'SELECT `idx`,
                             `id`,
                             `name`,
                             `email`,
                             `phone`,
                             CASE WHEN `sex` = ? then "남성" else "여성" end sex_name,
                             `sex`,
                             `birth`,
                             `join_date`,
                             `join_approval_date`,
							 `mileage`,
							 `account_no`,
							 `account_bank`
                      FROM `imi_members` 
                      WHERE `idx` = ?';
            $result = $this->db->execute($query, $param);
            
			if ($result == false) {
				return false;
			}
            
			return $result;
        }
        
        public function deleteMember($idx)
        {
            /**
			 * @author: LeeTaeHee
			 * @param:  회원 고유 키
			 * @brief: 회원탈퇴 처리 (update)
			 * @return: boolean
			 */
            
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

		public function getAccountByMember($idx)
		{
			/**
			 * @author: LeeTaeHee
			 * @param:  회원 고유 키
			 * @brief: 회원 계좌정보 가져오기
			 * @return: object
			 */
			$query = 'SELECT `idx`,
							 `account_no`,
							 `account_bank`
					  FROM `imi_members`
					  WHERE `idx` = ?
					';

			$result = $this->db->execute($query,$idx);

			if ($result == false) {
				return false;
			}
            
			return $result;
		}

		public function updateMyAccount($param)
		{
			/**
			 * @author: LeeTaeHee
			 * @param:  회원 고유 키, 계좌 내용
			 * @brief: 회원 계좌정보 수정하기
			 * @return: object
			 */

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

		public function updateMileageCharge($param)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 회원 PK, 변경 마일리지 금액
			 * @brief: 회원테이블 마일리지 변경 (충전시)
			 * @return: int 
			 */

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

		public function updateMileageWithdrawal($param)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 회원 PK, 변경 마일리지 금액
			 * @brief: 회원테이블 마일리지 변경 (충전시)
			 * @return: int 
			 */

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

		public function getMemberList()
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 없음
			 * @brief: 관리자 회원 현황 리스트
			 * @return: array 
			 */

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

			if ($result == false) {
				return false;
			}
            
			return $result;
		}
	}