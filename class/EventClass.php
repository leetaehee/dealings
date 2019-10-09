<?php
    /**
	 * @file EventClass.php
	 * @brief 이벤트 대한 클래스
	 * @author 이태희
	 */
	Class EventClass 
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
         * @brief: 이벤트 정의항목 받아오기 
         * @param: 이벤트타입, 날짜, 이벤트 종료여부를 담은 array
		 * @return: array
         */
		public function getEventData($param)
		{
			$query = 'SELECT *
					  FROM `imi_event` 
					  WHERE `start_date` <= ?
					  AND `end_date` >= ?
					  AND `event_type` = ?
					  AND `is_end` = ?';
			
			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}
			
			return $result;
		}

		/**
         * @brief: 이벤트 전체 리스트 가져오기 
         * @param: 사용여부
		 * @return: array
         */
		public function getEventList($param)
		{
			$query = 'SELECT * FROM `imi_event` WHERE `is_end` = ? ORDER BY `idx` DESC, `name` ASC';
			
			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}
			
			return $result;
		}

		/**
         * @brief: 이벤트 idx 체크 
         * @param: 이벤트 키 값
		 * @return: array
         */
		public function getEventDataByIdx($param)
		{
			$query = 'SELECT * FROM `imi_event` WHERE `idx` = ? AND `is_end` = ? FOR UPDATE';

			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}
			
			return $result;
		}

		/**
         * @brief: 이벤트 히스토리가 존재하는지 체크 
         * @param: 이벤트타입, 회원 키, 거래 키, 이벤트 타입 담은 array
		 * @return: string
         */
		public function getIsExistEventHistoryIdx($param)
		{
			$query = 'SELECT `idx`
					  FROM `imi_event_history`
					  WHERE `event_idx` = ?
					  AND `member_idx` = ?
					  AND `event_type` = ?';

			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}
			
			return $result->fields['idx'];
		}
		
		/**
         * @brief: 이벤트 히스토리 삽입 
         * @param: 이벤트타입, 회원 키, 거래 키, 이벤트 참여일자, 금액, 타입을 담은 array
		 * @return: int
         */
		public function insertEventHistory($param)
		{
			$query = 'INSERT INTO `imi_event_history` SET 
						`event_idx` = ?,
						`member_idx` = ?,
						`participate_date` = CURDATE(),
						`participate_datetime` = NOW(),
						`event_cost` = ?,
						`event_type` = ?,
						`participate_count` = ?';

			$result = $this->db->execute($query, $param);
			$inserId = $this->db->insert_id();

			if ($inserId < 1) {
				return false;
			}

			return $inserId;
		}

		/**
         * @brief: 이벤트 히스토리 수정 
         * @param: 이벤트 히스토리 키, 금액 담은 array
		 * @return: int
         */
		public function updateEventHistory($param)
		{
			$query = 'UPDATE `imi_event_history` SET 
						`event_cost` = `event_cost` + ?,
						`participate_count` = `participate_count` + ?
						WHERE `idx` = ?';
			
			$result = $this->db->execute($query, $param);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}

		/**
         * @brief: 이벤트 종료 정보 수정  
         * @param: 이벤트 키, 이벤트 종료엽여부를 담은 array
		 * @return: int
         */
		public function updateEventIsEnd($param)
		{
			$query = 'UPDATE `imi_event` SET 
						`is_end` = ?
						WHERE `idx` = ?';
			
			$result = $this->db->execute($query, $param);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}
			return $affected_row;
		}

		/**
         * @brief: 이벤트 히스토리 출력 
         * @param: 이벤트 키 값
		 * @return: array
         */
		public function getEventHistoryList($param)
		{
			$query = 'SELECT `im`.`name`,
							 `ieh`.`participate_count`,
							 `ieh`.`event_cost`,
							 `ieh`.`event_type`,
                             `ieh`.`member_idx`
					  FROM `imi_event_history` `ieh`
						INNER JOIN `imi_members` `im`
							ON `ieh`.`member_idx` = `im`.`idx`
					  WHERE `ieh`.`event_idx` = ?
					  ORDER BY `ieh`.`event_cost` DESC, `ieh`.`participate_count` DESC
					  LIMIT ?
                      FOR UPDATE ';
			
			$result = $this->db->execute($query, $param);

			if ($result === false) {
				return false;
			}
			
			return $result;
		}

		/**
         * @brief: 이벤트 결과 출력 
         * @param: 이벤트 키 값
		 * @return: array
         */
		public function getEventResultList($eventIdx)
		{
			$query = 'SELECT `im`.`name`,
							 `ier`.`event_cost`,
                             `ier`.`member_idx`
					  FROM `imi_event_result` `ier`
						INNER JOIN `imi_members` `im`
							ON `ier`.`member_idx` = `im`.`idx`
					  WHERE `ier`.`event_idx` = ?
					  ORDER BY `ier`.`grade` ASC
                      FOR UPDATE ';
			
			$result = $this->db->execute($query, $eventIdx);

			if ($result === false) {
				return false;
			}
			
			return $result;
		}
        
        /**
         * @brief: 이벤트 결과 테이블 입력 중복체크  
         * @param: 이벤트 키 값
		 * @return: int 
         */
        public function getCheckEventResultData($eventIdx)
        {
            $query = 'SELECT COUNT(`idx`) `result_count` FROM `imi_event_result` WHERE `event_idx` = ? FOR UPDATE';
            
            $result = $this->db->execute($query, $eventIdx);

			if ($result === false) {
				return false;
			}
			
			return $result->fields['result_count'];            
        }
        
        public function insertEventResult($param)
        {
            $query = 'INSERT INTO `imi_event_result` SET 
                        `event_idx` = ?,
                        `member_idx` = ?,
                        `event_cost` = ?,
                        `event_rate` = ?,
                        `grade` = ?,
                        `process_date` = CURDATE(),
                        `process_datetime` = NOW()';
            
            $result = $this->db->execute($query, $param);
			$inserId = $this->db->insert_id();

			if ($inserId < 1) {
				return false;
			}

			return $inserId;
        }
	}
		