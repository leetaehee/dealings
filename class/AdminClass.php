<?php
	/**
	 * @file AdminClass.php
	 * @brief 관리자 클래스, 관리자에 대한 기능 서술
	 * @author 이태희
	 */
	Class AdminClass 
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
		public function checkAdminFormValidate($postData)
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
                      FROM `imi_admin`
                      WHERE `phone` = ? OR `email` = ? {$search} FOR UPDATE";
            $result = $this->db->execute($query,$accountData);
            
			if ($result === false) {
				return false;
			}
			
			return $result->fields['cnt'];
        }
		
		/**
		 * @brief: 회원 가입 시 중복 체크(아이디)
		 * @param: id, db객체
		 * @return: true/false
		 */
		public function getIdOverlapCount($id)
		{
			$query = "SELECT count(id) cnt FROM `imi_admin` WHERE `id` = ?";
			$result = $this->db->execute($query,$id);

			if ($result == false) {
				return false;
			}
			
			return $result->fields['cnt'];
		}

		/**
		 * @brief: 회원 가입 시 중복 체크(휴대폰)
		 * @param: phone, db객체
		 * @return: true/false
		 */
		public function getPhoneOverlapCount($phone)
		{	
			$phone = removeHyphen($phone);

			$query = "SELECT count(phone) cnt FROM `imi_admin` WHERE `phone` = ?";
			$result = $this->db->execute($query, setEncrypt($phone));

			if ($result == false) {
				return false;
			}
			return $result->fields['cnt'];
		}
		
		/**
		 * @brief: 회원 가입 시 중복 체크(이메일)
		 * @param: email, db객체
		 * @return: true/false
		 */
		public function getEmailOverlapCount($email)
		{	
			$query = "SELECT count(phone) cnt FROM `imi_admin` WHERE `email` = ?";
			$result = $this->db->execute($query, setEncrypt($email));

			if ($result == false) {
				return false;
			}
			return $result->fields['cnt'];
		}

		/**
		 * @brief: 회원 가입 저장
		 * @param: form 데이터
		 * @return: true/false
		 */
		public function insertMember($postData)
		{
			//코드값 참조: grade_code = 3 (신규 가입시 부여되는 가장 낮은 레벨)
			$bindValue = [$postData['id'],
						  password_hash($postData['password'], PASSWORD_DEFAULT),
						  setEncrypt($postData['email']),
						  setEncrypt($postData['name']),
						  setEncrypt($postData['phone']),
						  $postData['sex'],
						  setEncrypt($postData['birth'])
						];

			$query = "INSERT INTO `imi_admin` SET
						`id` = ?,
						`password` = ?,
						`email` = ?,
						`name` = ?,
						`phone` = ?,
						`sex` = ?,
						`birth` = ?,
						`join_date` = CURDATE()
					 ";

			$result = $this->db->execute($query,$bindValue);
			$inserId = $this->db->insert_id(); // 추가

			if ($inserId < 1) {
				return false;
			}

			return $inserId;
		}
		
		/**
		 * @brief: 관리자 수정
		 * @param: form 데이터
		 * @return: int 
		 */
		public function updateAdmin($postData)
		{
			$param = [
				password_hash($postData['password'], PASSWORD_DEFAULT),
				setEncrypt($postData['email']),
				setEncrypt($postData['phone']),
				setEncrypt($postData['name']),
				$postData['sex'],
				setEncrypt($postData['birth']),
				$postData['idx']
			];

			$query = 'UPDATE `imi_admin` SET
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
			$query = 'SELECT `join_approval_date` FROM `imi_admin` WHERE `idx` = ? FOR UPDATE';
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
			$query = 'UPDATE `imi_admin` SET `join_approval_date` = CURDATE() WHERE `idx` = ?'; 
			$result = $this->db->execute($query,$idx);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}
        
		/**
		 * @brief: 관리자 개인정보 가져오기
		 * @param:  회원 고유 키
		 * @return: array
		 */
        public function getAdminData($idx)
		{
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
							 `is_superadmin`
                      FROM `imi_admin` 
                      WHERE `idx` = ?';
            $result = $this->db->execute($query, $param);
            
			if ($result === false) {
				return false;
			}
			return $result;
        }
        
		/**
		 * @brief: 관리자 탈퇴 처리 (update)
		 * @param:  관리자 고유 키
		 * @return: boolean
		 */
		public function deleteAdmin($idx)
        {
            $query = 'UPDATE `imi_admin` 
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
	}