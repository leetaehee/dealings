<?php
	/**
	 * @file LoginClass.php
	 * @brief Login/Logout에 대한 클래스
	 * @author 이태희
	 */
	Class LoginClass 
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
		 * @brief: 로그인 정보가 맞는지 체크 
		 * @param: 로그인 데이터 
		 * @return: true/false
		 */
		public function getIsLogin($id)
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
						FROM `imi_members` `imi`
							INNER JOIN `imi_member_grades` `img`
								ON `imi`.`grade_code` = `img`.`grade_code`
						WHERE `imi`.`id` = ?
						FOR UPDATE
					';

			// 탈퇴일 AND `imi`.`withdraw_date` IS NULL
			// 강제탈퇴일 AND `imi`.`is_forcedEviction` = ?
            // 가입일 AND `imi`.`join_approval_date` IS NOT NULL
			
			$result = $this->db->execute($query, $id);

			if ($result == false) {
				return false;
			}

			return $result;
		}
		
		/**
		 * @brief: 로그인 정보가 맞는지 체크
		 * @param: 관리자 PK (admin) 
		 * @return: true/false
		 */
		public function getIsAdminLogin($id)
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
						FROM `imi_admin`
						WHERE `id` = ?
						FOR UPDATE
					';

			$result = $this->db->execute($query, $id);

			if ($result == false) {
				return false;
			}

			return $result;
		}

		/**
		 * @brief: 유효성 검증
		 * @param: 로그인 폼 데이터(array)
		 * @return: true/false
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
		 * @brief: 로그인 시 로그인 이력 남기기
		 * @param: 회원의 접속 정보(array)
		 * @return: int
		 */
		public function insertIP($param)
		{
			$query = ' insert into `imi_access_ip` set
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
		 * @brief: 로그인 시 로그인 이력 남기기 (admin)
		 * @param: 회원의 접속 정보(array)
		 * @return: int
		 */
		public function insertAdminIP($param)
		{
			$query = ' insert into `imi_admin_access_ip` set
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
		 * @brief: 사용자가 입력한 비밀번호가 맞는지 체크 
		 * @param: 패스워드
		 * @return: boolean
		 */
        public function checkPasswordByUser($param)
        {
            $query = 'SELECT `password` FROM `imi_members` where `idx` = ? FOR UPDATE';
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
		 * @brief: 관리자가 입력한 비밀번호가 맞는지 체크
		 * @param: 패스워드
		 * @return: boolean
		 */
		public function checkPasswordByAdmin($param)
        {
            $query = 'SELECT `password` FROM `imi_admin` where `idx` = ? FOR UPDATE';
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
		 * @brief: 사용자의 로그인 기록 
		 * @param: 회원PK, 접근일자 (array)
		 * @return: array
		 */
		public function getLoginAccessList($param)
		{
			$query = 'SELECT `idx`,
							 `member_idx`,
							 `access_ip`,
							 `access_date`,
							 `access_datetime`
					  FROM `imi_access_ip`
					  WHERE `member_idx` = ?
					  AND `access_date` = ?
					  FOR UPDATE
					';
			
			$result = $this->db->execute($query, $param);

			if ($result === false) {
                return false;
            } 

			return $result;
		}

		/**
		 * @brief: 관리자의 로그인 기록
		 * @param: 관리자PK, 접근일자 (array)
		 * @return: array
		 */
		public function getAdminLoginAccessList($param)
		{
			$query = 'SELECT `idx`,
							 `admin_idx`,
							 `access_ip`,
							 `access_date`,
							 `access_datetime`
					  FROM `imi_admin_access_ip`
					  WHERE `admin_idx` = ?
					  AND `access_date` = ?
					  FOR UPDATE
					';
			
			$result = $this->db->execute($query, $param);

			if ($result === false) {
                return false;
            } 

			return $result;
		}
	}