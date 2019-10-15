<?php
	/**
	 * 관리자 기능
	 */
	Class AdminClass 
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
		* 관리자 회원가입 시 유효성 검증
		*
		* @param array $postata 회원가입 데이터
		*
		* @return array
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
		 * 회원 가입 시 중복체크 
		 *
		 * @param array $id, $email, $phone
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
                      FROM `imi_admin`
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
		 * 관리자 id 중복검사
		 *
		 * @param $id 아이디 
		 *
		 * @return int/bool
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
		 * 관리자 핸드폰 중복검사 
		 *
		 * @param string $phone
		 *
		 * @return int/bool
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
		 * 관리자 이메일 중복검사
		 *
		 * @param string $email
		 *
		 * @return int/bool
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
		 * 관리자 정보 추가 
		 *
		 * @param array $postData 회원가입 데이터
		 *
		 * @return int/bool
		 */
		public function insertMember($postData)
		{
			$bindValue = [
				$postData['id'],
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
		 * 관리자 정보 수정 
		 *
		 * @param array $postData 관리자 정보 데이터
		 *
		 * @return int/bool
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
		 * 회원가입 메일 승인을 했는지 체크 
		 *
		 * @param  int $idx  회원가입 고유 키
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
		 *
		 * @return int/bool
		 */
		public function getJoinApprovalMailDate($idx, $isUseForUpdate = false)
		{
			$query = 'SELECT `join_approval_date` FROM `imi_admin` WHERE `idx` = ?';

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
		 * 회원가입 메일 인증 완료 처리 
		 *
		 * @param int $idx 회원 가입 시 고유 키
		 *
		 * @return int/bool
		 */
		public function updateJoinApprovalMailDate($idx)
		{
			$query = 'UPDATE `imi_admin` SET `join_approval_date` = CURDATE() WHERE `idx` = ?';

			$result = $this->db->execute($query, $idx);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}

		/**
		 * 관리자 정보 출력
		 *
		 * @param int $idx 관리자 고유 키
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
		 *
		 * @return array/bool
		 */
        public function getAdminData($idx, $isUseForUpdate = false)
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
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

            $result = $this->db->execute($query, $param);
            
			if ($result === false) {
				return false;
			}
			return $result;
        }

		/**
		 * 관리자 탈퇴 
		 *
		 * @param int $idx 관리자 고유 키 
		 *
		 * @return int/booelan
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