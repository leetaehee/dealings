<?php
	/**
	 * 이벤트 클래스
	 */
	Class EventClass 
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
		 * 이벤트 히스토리 중복 체크 
		 *
		 * @param array 이벤트 키, 회원 키, 이벤트 타입 정보
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
		 *
		 * @return int/bool
		 */
		public function getIsExistEventHistoryIdx($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `idx`
					  FROM `imi_event_history`
					  WHERE `member_idx` = ?
					  AND `event_type` = ?';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}
			
			return $result->fields['idx'];
		}

		/**
		 * 이벤트 히스토리 추가
		 *
		 * @param array 이벤트 히스토리 입력 데이터
		 *
		 * @return int/bool
		 */
		public function insertEventHistory($param)
		{
			$query = 'INSERT INTO `imi_event_history` SET 
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
		 * 이벤트 히스토리 수정 
		 *
		 * @param 이벤트 키, 이벤트 금액, 참여횟수 정보
		 *
		 * @return int/bool
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
		 * 이벤트 히스토리 LIMIT를 통해  원하는 만큼 출력
		 *
		 * @param array 이벤트 키, 출력 수 정보
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
		 *
		 * @return array/bool
		 */
		public function getEventHistoryList($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `im`.`name`,
							 `ieh`.`participate_count`,
							 `ieh`.`event_cost`,
							 `ieh`.`event_type`,
                             `ieh`.`member_idx`
					  FROM `imi_event_history` `ieh`
						INNER JOIN `imi_members` `im`
							ON `ieh`.`member_idx` = `im`.`idx`
					  WHERE `ieh`.`event_type` = ?
					  ORDER BY `ieh`.`event_cost` DESC, `ieh`.`participate_count` DESC
					  LIMIT ?';

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
		 * 함수 정리 --- 
		 */

		/**
		 * 가입 1달 미만 회원에게 주어지는 구매 이벤트 진행여부 확인
		 *
		 * @param array $param  이벤트에 필요한 항목을 배열로 전달
		 *
		 * @return array/boolean
		 */
		public function onProvideEvent($param)
		{
			$isJoinPeriodExceed = false;
			$today = date('Y-m-d');

			$couponIdx = $param['couponIdx'];
			$commisionCouponIdx = $param['commisionCouponIdx'];
			$memberIdx = $param['member_idx'];
			$itemNo = $param['itemNo'];
			$CONFIG_EVENT_ARRAY = $param['CONF_EVENT_ARRAY'];

			$isParticipatIngEvent = false;
			$payback = 0;

			$rJoinDateQ = 'SELECT `join_approval_date` FROM `imi_members` WHERE `idx` = ?';
			$rJoinDateResult = $this->db->execute($rJoinDateQ, $memberIdx);
			if ($rJoinDateResult == false) {
				return [
					'result'=> false,
					'resultMessage'=> '가입승인일자를 조회 하면서 오류가 발생했습니다.'
				];
			}

			$joinApprovalDate = $rJoinDateResult->fields['join_approval_date'];
			$joinAfterApprovalDate = date('Y-m-d', strtotime('+1 months',strtotime($joinApprovalDate)));

			if ($today < $joinAfterApprovalDate) { 
				$isJoinPeriodExceed = true; // 회원이 가입한지 1달이 안되었다면 구매 이벤트 적용
			}

			if ($isJoinPeriodExceed === true) { 
				/** 쿠폰 사용하지 않은 경우에만 해당 됨(구매자 쿠폰). */
				if (empty($couponIdx)){				
					// 이벤트 기간 체크 (구매)
					$event = $CONFIG_EVENT_ARRAY['구매'];
			
					if ($event['start_date'] <= $today && $event['end_date'] >= $today) {
						$rSellItemQ = 'SELECT `payback` FROM `imi_sell_item` WHERE `idx` = ? ';

						$rSellItemResult = $this->db->execute($rSellItemQ, $itemNo);
						if ($rSellItemResult === false) {
							return [
								'result'=> false,
								'resultMessage'=> '페이백 정보를 조회하면서 오류가 발생했습니다.'
							];
						}

						// 이벤트 진행중이며, 페이백이 설정되어있는 경우만 이벤트 진행하도록 함.
						$payback = $rSellItemResult->fields['payback'];
						if ($payback > 0) {
							$isParticipatIngEvent = true;
						}
					} 
				}

				/** 쿠폰을 사용하지 않은 경우에만 해당 됨(판매자 쿠폰) */
				if (empty($commisionCouponIdx)) {
					// 이벤트 기간 체크 (판매)
					$event = $CONFIG_EVENT_ARRAY['판매'];
			
					if ($event['start_date'] <= $today && $event['end_date'] >= $today) {
						$isParticipatIngEvent = true;
					}
				}
			}

			return [
				'result'=> true,
				'isParticipatIngEvent'=> $isParticipatIngEvent,
				'payback'=> $payback,
				'paybackMileageType'=> 8
			];
		}

		/**
		 * 이벤트 히스토리 추가 (있는 경우는 수정)
		 *
		 * @param array $param 이벤트 히스토리에 추가 될 항목을 배열로 전달
		 *
		 * @return bool
		 */
		public function addEventHistory($param)
		{
			$rHistoryParam = [
				'member_idx'=> $param['member_idx'],
				'type'=> $param['event_type']
			];


			$rEventHistoryQ = 'SELECT `idx` 
							   FROM `imi_event_history`
							   WHERE `member_idx` = ?
							   AND `event_type` = ?
							   FOR UPDATE';
			
			$rEventHistoryResult = $this->db->execute($rEventHistoryQ, $rHistoryParam);
			if ($rEventHistoryResult === false) {
				return [
					'result'=> false,
					'resultMessage'=> '이벤트 히스토리 테이블을 조회하면서 오류가 발생했습니다.'
				];
			}
	
			$historyBuyerIdx = $rEventHistoryResult->fields['idx'];
			if (!empty($historyBuyerIdx)) {
				$uHistoryParam = [
					'event_cost'=> $param['paybackDealings'],
					'partcipate_count'=> 1,
					'eventHistoryIdx'=> $historyBuyerIdx
				];

				$uEventHistoryQ = 'UPDATE `imi_event_history` SET 
									`event_cost` = `event_cost` + ?,
									`participate_count` = `participate_count` + ?
								   WHERE `idx` = ?';
			
				$uEventHistoryResult = $this->db->execute($uEventHistoryQ, $uHistoryParam);
				$historyAffectedRow = $this->db->affected_rows();

				if ($historyAffectedRow < 1) {
					return [
						'result'=> false,
						'resultMessage'=> '이벤트 히스토리 테이블 수정하면서 오류가 발생했습니다.'
					];
				}
			} else {
				$cHistoryParam = [
					'member_idx'=> $param['member_idx'],
					'event_cost'=> $param['paybackDealings'],
					'event_type'=> '구매',
					'partcipate_count'=> 1
				];

				$cEventHistoryQ = 'INSERT INTO `imi_event_history` SET 
									`member_idx` = ?,
									`participate_date` = CURDATE(),
									`participate_datetime` = NOW(),
									`event_cost` = ?,
									`event_type` = ?,
									`participate_count` = ?';

				$cEventHistoryResult = $this->db->execute($cEventHistoryQ, $cHistoryParam);
				$inserId = $this->db->insert_id();

				if ($inserId < 1) {
					return [
						'result'=> false,
						'resultMessage'=> '이벤트 히스토리 테이블 추가하면서 오류가 발생했습니다.'
					];
				}
			}

			return [
				'result'=> true,
				'resultMessage'=> ''
			];
		}
	}
		