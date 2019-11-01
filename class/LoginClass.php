<?php
	/**
     * 로그인 클래스
     */ 
	Class LoginClass 
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
         * 로그인 정보 체크
         *
         * @param string $id
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool 
         */
		public function getIsLogin($id, $isUseForUpdate = false)
		{ 
			$query = 'SELECT `imi`.`id`,
							 `imi`.`idx`,
							 `imi`.`password`,
							 `imi`.`name`,
							 `imi`.`grade_code`,
							 `imi`.`is_forcedEviction`,
							 `imi`.`forcedEviction_date`,
                             `imi`.`join_date`,
							 `imi`.`join_approval_date`,
							 `imi`.`withdraw_date`
						FROM `th_members` `imi`
							INNER JOIN `th_member_grades` `img`
								ON `imi`.`grade_code` = `img`.`grade_code`
						WHERE `imi`.`id` = ?';

			// 탈퇴일 AND `imi`.`withdraw_date` IS NULL
			// 강제탈퇴일 AND `imi`.`is_forcedEviction` = ?
            // 가입일 AND `imi`.`join_approval_date` IS NOT NULL

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query, $id);

			if ($result == false) {
				return false;
			}

			return $result;
		}
		
        /**
         * 관리자 로그인 정보 체크
         * 
         * @param string $id
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getIsAdminLogin($id, $isUseForUpdate = false)
		{ 
			$query = 'SELECT `id`,
							 `idx`,
							 `password`,
							 `name`,
							 `is_forcedEviction`,
							 `forcedEviction_date`,
							 `join_approval_date`,
							 `withdraw_date`,
							 `is_superadmin`
						FROM `th_admin`
						WHERE `id` = ?';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $id);

			if ($result == false) {
				return false;
			}

			return $result;
		}
        
        /**
         * 로그인 유효성 체크
         *
         * @param array $postData
         *
         * @return array
         */
		public function checkLoginFormValidate($postData)
		{
			if (isset($postData['id']) && empty($postData['id'])) {
				return ['isValid'=>false, 'errorMessage'=>'아이디를 입력하세요.'];
			}

			if (isset($postData['password']) && empty($postData['password'])) {
				return ['isValid'=>false, 'errorMessage'=>'패스워드를 입력하세요.'];
			}

			return ['isValid'=>true, 'errorMessage'=>''];
		}
        
        /**
         * 회원 접속 정보 삽입
         *
         * @param array $param
         *
         * @param int/bool
         */
		public function insertIP($param)
		{
			$query = ' insert into `th_access_ip` set
						`member_idx` = ?,
						`access_ip` = ?,
						`access_date` = CURDATE(),
						`access_datetime` = NOW(),
						`access_user_agent` = ?
					 ';
			
			$result = $this->db->execute($query,$param);
			$inserId = $this->db->insert_id(); 

			if ($inserId < 1) {
				return false;
			}

			return $inserId;
		}
        
        /**
         * 관리자 접속 정보 삽입
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function insertAdminIP($param)
		{
			$query = ' insert into `th_admin_access_ip` set
						`admin_idx` = ?,
						`access_ip` = ?,
						`access_date` = CURDATE(),
						`access_datetime` = NOW(),
						`access_user_agent` = ?
					 ';
			
			$result = $this->db->execute($query,$param);
			$inserId = $this->db->insert_id(); 

			if ($inserId < 1) {
				return false;
			}
			return $inserId;
		}
        
        /**
         * 사용자 비밀번호 체크 
         *
         * @param array $param
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
        public function checkPasswordByUser($param, $isUseForUpdate = false)
        {
            $query = 'SELECT `password` FROM `th_members` where `idx` = ?';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

            $result = $this->db->execute($query, $param['idx']);
            
            if ($result === false) {
                return false;
            } else {
                $dbpassword = $result->fields['password'];
                
                if (password_verify($param['password'], $dbpassword)==false) {
                    return null;
                }
            }        
            return $result;
        }

		/**
         * 관리자 비밀번호 체크 
         *
         * @param array $param
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function checkPasswordByAdmin($param, $isUseForUpdate = false)
        {
            $query = 'SELECT `password` FROM `th_admin` where `idx` = ?';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

            $result = $this->db->execute($query, $param['idx']);
            
            if ($result === false) {
                return false;
            } else {
                $dbpassword = $result->fields['password'];
                
                if (password_verify($param['password'], $dbpassword)==false) {
                    return null;
                }
            }        
            return $result;
        }
        
        /**
         * 사용자 로그인 기록 출력
         *
         * @param array $param
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getLoginAccessList($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `idx`,
							 `member_idx`,
							 `access_ip`,
							 `access_date`,
							 `access_datetime`
					  FROM `th_access_ip`
					  WHERE `member_idx` = ?
					  AND `access_date` = ?';

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
         * 관리자 로그인 기록 출력
         *
         * @param array $param
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getAdminLoginAccessList($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `idx`,
							 `admin_idx`,
							 `access_ip`,
							 `access_date`,
							 `access_datetime`
					  FROM `th_admin_access_ip`
					  WHERE `admin_idx` = ?
					  AND `access_date` = ?';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query, $param);

			if ($result === false) {
                return false;
            } 

			return $result;
		}
	}