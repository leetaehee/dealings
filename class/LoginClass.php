<?php
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
		
		public function __construct($db) 
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 커넥션 파라미터
			 * @brief: 데이터베이스 커넥션 생성 
			 */
			$this->db = $db;
		}

		public function getIsLogin($id)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 로그인 데이터 
			 * @brief: 로그인 정보가 맞는지 체크 
			 * @return: true/false
			 */

			//$param = [$id, 'N'];
			 
			$query = 'SELECT `imi`.`id`,
							 `imi`.`idx`,
							 `imi`.`password`,
							 `imi`.`name`,
							 `imi`.`grade_code`,
							 `imi`.`is_forcedEviction`,
							 `imi`.`forcedEviction_date`,
							 `imi`.`join_approval_date`,
							 `imi`.`withdraw_date`
						FROM `imi_members` `imi`
							INNER JOIN `imi_member_grades` `img`
								ON `imi`.`grade_code` = `img`.`grade_code`
						WHERE `imi`.`id` = ?
						AND `imi`.`join_approval_date` IS NOT NULL
					';

			// 탈퇴일 AND `imi`.`withdraw_date` IS NULL
			// 강제탈퇴일 AND `imi`.`is_forcedEviction` = ?
			
			$result = $this->db->execute($query, $id);

			if ($result == false) {
				return false;
			}

			return $result;
		}

		public function getIsAdminLogin($id)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 로그인 데이터 (admin)
			 * @brief: 로그인 정보가 맞는지 체크 
			 * @return: true/false
			 */
			 
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
						AND `join_approval_date` IS NOT NULL
					';

			$result = $this->db->execute($query, $id);

			if ($result == false) {
				return false;
			}

			return $result;
		}

		public function checkLoginFormValidate($postData)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 로그인 폼 데이터
			 * @brief: 유효성 검증
			 * @return: true/false
			 */

			if (isset($postData['id']) && empty($postData['id'])) {
				return ['isValid'=>false, 'errorMessage'=>'아이디를 입력하세요.'];
			}

			if (isset($postData['password']) && empty($postData['password'])) {
				return ['isValid'=>false, 'errorMessage'=>'패스워드를 입력하세요.'];
			}

			return ['isValid'=>true, 'errorMessage'=>''];
		}

		public function insertIP($param)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: ip, 회원고유번호, ip
			 * @brief: 로그인 시 로그인 이력 남기기
			 * @return: int
			 */

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

		public function insertAdminIP($param)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: ip, 고유번호, ip
			 * @brief: 로그인 시 로그인 이력 남기기 (admin)
			 * @return: int
			 */

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
        
        public function checkPasswordByUser($param)
        {
            /**
			 * @author: LeeTaeHee
			 * @param: 회원 PK, 입력받은 패스워드 배열
			 * @brief: 사용자가 입력한 비밀번호가 맞는지 체크 
			 * @return: boolean
			 */
   
            $query = 'SELECT `password` FROM `imi_members` where `idx` = ? FOR UPDATE';
            $result = $this->db->execute($query, $param[0]);
            
            if ($result == false) {
                return false;
            } else {
                $dbpassword = $result->fields['password'];
                
                if (password_verify($param[1], $dbpassword)==false) {
                    return false;
                }
            }        
            return $result;
        }

		public function checkPasswordByAdmin($param)
        {
            /**
			 * @author: LeeTaeHee
			 * @param: 관리자 PK, 입력받은 패스워드 배열
			 * @brief: 관리자가 입력한 비밀번호가 맞는지 체크 
			 * @return: boolean
			 */
   
            $query = 'SELECT `password` FROM `imi_admin` where `idx` = ? FOR UPDATE';
            $result = $this->db->execute($query, $param[0]);
            
            if ($result == false) {
                return false;
            } else {
                $dbpassword = $result->fields['password'];
                
                if (password_verify($param[1], $dbpassword)==false) {
                    return false;
                }
            }        
            return $result;
        }

		public function getLoginAccessList($param)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 회원 PK, 접근날짜 배열로 받기
			 * @brief: 사용자의 로그인 기록 
			 * @return: array
			 */

			$query = 'SELECT `idx`,
							 `member_idx`,
							 `access_ip`,
							 `access_date`,
							 `access_datetime`
					  FROM `imi_access_ip`
					  WHERE `member_idx` = ?
					  AND `access_date` = ?
					';
			
			$result = $this->db->execute($query, $param);

			if ($result == false) {
                return false;
            } 

			return $result;
		}

		public function getAdminLoginAccessList($param)
		{
			/**
			 * @author: LeeTaeHee
			 * @param: 관리자 PK, 접근날짜 배열로 받기
			 * @brief: 관리자의 로그인 기록 
			 * @return: array
			 */

			$query = 'SELECT `idx`,
							 `admin_idx`,
							 `access_ip`,
							 `access_date`,
							 `access_datetime`
					  FROM `imi_admin_access_ip`
					  WHERE `admin_idx` = ?
					  AND `access_date` = ?
					';
			
			$result = $this->db->execute($query, $param);

			if ($result == false) {
                return false;
            } 

			return $result;
		}
	}