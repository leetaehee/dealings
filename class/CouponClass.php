<?php
	/**
	 * 쿠폰 클래스
	 */
	Class CouponClass 
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
		 * 쿠폰 발행 시 유효성 검증
		 *
		 * @param array $postData 쿠폰 입력 폼 데이터
		 *
		 * @return array
		 */
		public function checkCouponFormValidate($postData)
		{
			if (isset($postData['voucher_name']) && empty($postData['voucher_name'])) {
				return ['isValid'=>false, 'errorMessage'=>'상품권을 선택하세요.'];
			}

			if (isset($postData['voucher_price']) && $postData['voucher_price'] == '') {
				return ['isValid'=>false, 'errorMessage'=>'상품권 가격을 선택하세요.'];
			}

			if (isset($postData['discount_rate']) && empty($postData['discount_rate'])) {
				return ['isValid'=>false, 'errorMessage'=>'할인율 선택하세요.'];
			}

			if (isset($postData['coupon_subject']) && empty($postData['coupon_subject'])) {
				return ['isValid'=>false, 'errorMessage'=>'발행쿠폰명칭을 입력하세요.'];
			}

			if (isset($postData['start_date']) && empty($postData['start_date'])) {
				return ['isValid'=>false, 'errorMessage'=>'쿠폰 적용일자를 입력하세요.'];
			}

			if (isset($postData['expiration_date']) && empty($postData['expiration_date'])) {
				return ['isValid'=>false, 'errorMessage'=>'쿠폰 만료일자를 입력하세요.'];
			}

			return ['isValid'=>true, 'errorMessage'=>''];
		}

		/**
		 * 상품권 값에 대해서 유효성 체크
		 *
		 * @param array $param 상품권 데이터
		 *
		 * @return array
		 */
		public function checkVoucherValidate($param)
		{
			if (isset($param['voucher_name']) && !empty($param['voucher_name'])) {
				if (!in_array($param['voucher_name'], $param['voucher_array'])) {
					return ['isValid'=>false, 'errorMessage'=>'유효하지 않은 상품권이 들어왔습니다.'];
				}
			}

			if (isset($param['voucher_price']) && !empty($param['voucher_price'])) {
				if (!in_array($param['voucher_price'], $param['money_array'])) {
					return ['isValid'=>false, 'errorMessage'=>'유효하지 않은 상품권 금액이 들어왔습니다.'];
				}	
			}

			if (isset($param['coupon_issue_type']) && !empty($param['coupon_issue_type'])) {
				if (!in_array($param['coupon_issue_type'], $param['coupon_issue_array'])) {
					return ['isValid'=>false, 'errorMessage'=>'유효하지 않은 발행쿠폰타입이 들어왔습니다.'];
				}	
			}
			return ['isValid'=>true, 'errorMessage'=>''];
		}

		/**
		 * 쿠폰 타입 발행
		 *
		 * @param array $param  쿠폰 타입 생성하기 위한 데이터
		 *
		 * @return int/bool
		 */
		public function insertCupon($param)
		{
			$query = 'INSERT INTO `imi_coupon` SET 
						`issue_type` = ?,
						`sell_item_idx` = ?,
						`subject` = ?,
						`item_money` = ?,
						`discount_rate` = ?,
						`discount_mileage` = ?,
						`start_date` = ?,
						`expiration_date` = ?,
						`issue_date` = CURDATE()';
			
			$result = $this->db->execute($query,$param);
			$inserId = $this->db->insert_id();

			if ($inserId < 1) {
				return false;
			}
			return $inserId;
		}

		/**
		 * 이용 가능한 쿠폰 조회 (해피머니 상품권은 해피머니쿠폰만 조회되어야 함)
		 *
		 * @param array  쿠폰 조회에 필요한 데이터 
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
		 *
		 * @return array/bool
		 */
		public function getAvailableCouponData($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `icm`.`idx`,
							 `icm`.`subject`,
							 `icm`.`item_money`,
							 `icm`.`discount_rate`
					   FROM `imi_coupon_member` `icm` 
					   WHERE `icm`.`sell_item_idx` IN (?,5)  
					   AND `icm`.`issue_type` = ?
					   AND `icm`.`item_money` IN (?,0)
                       AND `icm`.`is_coupon_del` = ?
					   AND `icm`.`is_del` = ?
                       AND `icm`.`member_idx` = ?
					   AND `icm`.`coupon_status` = ?';

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
		 * 쿠폰 타입,키 삭제여부 검증
		 *
		 * @param array $param 쿠폰 타입과 키, 삭제여부 
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
		 *
		 * @return int/booelan
		 */
		public function getCheckValidCoupon($param, $isUseForUpdate = false)
		{	
			$query = 'SELECT `idx`
					  FROM `imi_coupon`
					  WHERE `issue_type` = ?
					  AND `idx` = ?
					  AND `is_del` = ?';
			
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
		 * 쿠폰을 사용했는지 체크
		 *
		 * @param array $param 쿠폰 사용유무를 체크하기 위한 정보 
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
		 * 
		 * @return int/bool
		 */
		public function getCheckAvailableCoupon($param, $isUseForUpdate = false)
		{
			$query  = 'SELECT `icu`.`member_idx`
					   FROM `imi_coupon` `ic`
							LEFT JOIN `imi_coupon_useage` `icu`
								ON `ic`.idx = `icu`.`coupon_idx`
					   WHERE `ic`.`idx` = ? 
					   AND `ic`.`is_del` = ?
					   AND `icu`.`member_idx` = ?
					   AND `icu`.`is_refund` = ?
					   AND `icu`.`coupon_use_end_date` is not null';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result->fields['member_idx'];
		}

		/**
		 * 쿠폰 정보 출력 (PROCESS)
		 *
		 * @param int $couponIdx 쿠폰 키 
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
		 *
		 * @return int/bool
		 */
		public function getCouponDiscountData($couponIdx, $isUseForUpdate = false)
		{
			$query = 'SELECT `discount_mileage`,
							 `discount_rate`,
							 `item_money`
					  FROM `imi_coupon` 
					  WHERE `idx` = ?';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query, $couponIdx);
			if ($result === false) {
				return false;
			}

			return $result;
		}

		/**
		 * 쿠폰 사용내역 추가
		 *
		 * @param array $param 쿠폰 사용내역에 필요한 정보
		 *
		 * @return int/bool
		 */
		public function insertCouponUseage($param)
		{
			$query = 'INSERT INTO `imi_coupon_useage` SET 
						`issue_type` = ?,
						`dealings_idx` = ?,
						`coupon_idx` = ?,
						`member_idx` = ?,
						`coupon_use_before_mileage` = ?,
						`coupon_use_mileage` = ?,
						`coupon_use_start_date` = CURDATE(),
						`coupon_member_idx` = ?';
			
			$result = $this->db->execute($query, $param);
			$inserId = $this->db->insert_id();

			if ($inserId < 1) {
				return false;
			}
			return $inserId;
		}

		/**
		 * 쿠폰 취소 처리
		 *
		 * @param array $param 쿠폰 취소시 필요한 정보
		 *
		 * @return int/array
		 */
		public function updateCouponStatus($param)
		{
			$query = 'UPDATE `imi_coupon_useage` SET 
						`coupon_use_end_date` = ?,
						`is_refund` = ?
					   WHERE `idx` = ?';
			
			$result = $this->db->execute($query,$param);

			$affected_row = $this->db->affected_rows();
			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}

		/**
		 * 쿠폰 전체 사용내역 리스트
		 *
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
		 *
		 * @return array/bool
		 */
		public function getCouponUseList($isUseForUpdate = false)
		{
			$query = 'SELECT `ic`.`subject`,
							 `ic`.`item_money`,
							 `ic`.`discount_rate`,
							 `ic`.`discount_mileage`,
							 `icu`.`coupon_use_mileage`,
							 `icu`.`coupon_use_before_mileage`,
							 `icu`.`issue_type`,
							 `isi`.`item_name`,
							 `im`.`name`,
							 `icu`.`is_refund`,
							 `icu`.`coupon_use_end_date`
					  FROM `imi_coupon_useage` `icu`
						INNER JOIN `imi_coupon` `ic`
							ON `icu`.`coupon_idx` = `ic`.`idx`
						INNER JOIN `imi_sell_item` `isi`
							ON `ic`.`sell_item_idx` = `isi`.`idx`
						INNER JOIN `imi_members` `im`
							ON `icu`.`member_idx` = `im`.`idx`
					  ORDER BY `ic`.`subject` ASC, `ic`.`idx` DESC, `icu`.`coupon_use_end_date` DESC';
			
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
		 * 쿠폰 발행내역 리스트
		 *
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
		 *
		 * @return array 
		 */
		public function getCouponIssueList($isUseForUpdate = false)
		{
			$query = 'SELECT `ic`.`subject`,
							 `ic`.`item_money`,
							 `ic`.`discount_rate`,
							 `ic`.`discount_mileage`,
							 `ic`.`issue_type`,
							 `ic`.`is_del`,
							 `ic`.`issue_date`,
							 `ic`.`start_date`,
							 `ic`.`expiration_date`,
							 `isi`.`item_name`
					  FROM `imi_coupon` `ic`
						INNER JOIN `imi_sell_item` `isi`
							ON `ic`.`sell_item_idx` = `isi`.`idx`
					  ORDER BY `ic`.`issue_date` DESC, `ic`.`subject` ASC';

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
		 * 유효기간 지난 데이터가 얼마나 있는지 카운트
		 *
		 * @param array 쿠폰 종료일과 삭제여부 정보
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
		 *
		 * @return int/bool
		 */
		public function getCheckCouponValidDateCount($param, $isUseForUpdate = false)
		{
			$query = 'SELECT count(`idx`) `cnt` 
					  FROM `imi_coupon` 
					  WHERE `expiration_date` < ?  
					  AND `is_del` <> ?';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $param);

			if ($result === false) {
				return false;
			}

			return $result->fields['cnt'];
		}

		/**
		 * 유효기간이 지나서 삭제 해야할 쿠폰 리스트 (crontab)
		 *
		 * @param array 쿠폰삭제일과 삭제여부 정보 
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
		 *
		 * @return array/bool
		 */
		public function getCheckCouponValidDateList($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `ic`.`idx`
					  FROM `imi_coupon` `ic`
						LEFT JOIN `imi_coupon_member` `icm`
							ON `ic`.`idx` = `icm`.`coupon_idx`
					  WHERE `ic`.`expiration_date` < ?  
					  AND `ic`.`is_del` = ?
					  AND `icm`.`idx` IS NOT NULL';
			
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
		 * 쿠폰 삭제처리
		 * 
		 * @param array $param 쿠폰 삭제일과 종료일자 (crontab)
		 *
		 * @return int/bool
		 */
		public function updateCouponDelete($param){
			$query = 'UPDATE `imi_coupon` SET 
						`is_del` = ?
					   WHERE `expiration_date` < ?
					   AND `is_del` <> ?';
			
			$result = $this->db->execute($query, $param);

			$affected_row = $this->db->affected_rows();
			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}

		/**
		 * 유효기간이 지나서 삭제된 쿠폰은 지급된 리스트에서도 삭제 한다 (crontab)
		 *
		 * @param array @param 쿠폰 삭제여부와 키 정보
		 *
		 * @return int/bool
		 */
		public function updateCouponMemberDelete($param)
		{
			foreach($param as $key => $value){
				$updateParam = [
					'is_coupon_del'=> 'Y',
					'coupon_idx'=> $value['idx']
				];
				
				$query = 'UPDATE `imi_coupon_member` SET 
							`is_coupon_del` = ?
						   WHERE `coupon_idx` = ?';

				$result = $this->db->execute($query, $updateParam);

				$affected_row = $this->db->affected_rows();
				if ($affected_row < 1) {
					return false;
				}
			}
			return $affected_row;
		}

		/**
		 * 금액과 상품권 종류를 받아서 쿠폰이 유효한지 체크
		 *
		 * @param array $param  쿠폰의 가격과 종류 정보
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
		 *
		 * @return int/false
		 */
		public function getCheckUserCouponMatch($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `idx` FROM `imi_coupon` WHERE `item_money` = ? AND `sell_item_idx` = ?';

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
		 * 회원에게 지급가능한 쿠폰 리스트를 보여준다.
		 *
		 * @param array $param 회원키와 삭제여부 정보
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
		 *
		 * @return int/bool
		 */
		public function getMemberAvailableCouponList($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `ic`.`idx`,
							 `ic`.`issue_type`,
							 `ic`.`subject`,
							 `ic`.`item_money`,
							 `ic`.`discount_rate`,
							 `ic`.`discount_mileage`,
							 `isi`.`item_name`,
							 `icm`.`idx` `coupon_idx`
					  FROM `imi_coupon` `ic`
						LEFT JOIN `imi_coupon_member` `icm`
							ON `ic`.`idx` = `icm`.`coupon_idx`
							AND `icm`.`member_idx` = ?
							AND `icm`.`is_del` = ?
						LEFT JOIN `imi_sell_item` `isi`
							ON `ic`.`sell_item_idx` = `isi`.`idx`
				      WHERE `icm`.`idx` IS NULL
					  AND `ic`.`is_del` = ?';

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
         * 쿠폰 상세내여을 보여준다. (사용자 화면에서 사용되는 쿼리)
         *
         * @param int $couponIdx  쿠포의 키 값
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getMemberCouponData($couponIdx, $isUseForUpdate = false)
		{
			$query = 'SELECT `ic`.`idx`,
							 `ic`.`issue_type`,
							 `ic`.`subject`,
							 `ic`.`item_money`,
							 `ic`.`discount_rate`,
							 `ic`.`discount_mileage`,
							 `ic`.`sell_item_idx`,
							 `ic`.`is_del`,
							 `isi`.`item_name`
					  FROM `imi_coupon` `ic`
						LEFT JOIN `imi_sell_item` `isi`
							ON `ic`.`sell_item_idx` = `isi`.`idx`
				      WHERE `ic`.`idx`= ?';
            
            if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
            
			$result = $this->db->execute($query, $couponIdx);
			if ($result === false) {
				return false;
			}
			return $result;
		}
        
        /** 
         * 쿠폰의 키가 유효한지 확인
         *
         * @param int $couponIdx 쿠폰의 키 
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return int/bool
         */
		public function getValidCouponIdx($couponIdx, $isUseForUpdate = false)
		{
			$query = 'SELECT `idx` FROM `imi_coupon` WHERE `idx` = ?';
            
            if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
            
			$result = $this->db->execute($query, $couponIdx);
			if ($result === false) {
				return false;
			}
			return $result->fields['idx'];
		}
        
        /** 
         * 쿠폰의 상태 코드 가져오기
         *
         * @param 쿠폰 상태 코드명
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return int/bool
         */
		public function getCouponStatusCode($couponStatusName, $isUseForUpdate = false)
		{
			$query = 'SELECT `idx` FROM `imi_coupon_status_code` WHERE `coupon_status_name` = ?';
            
            if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
            
			$result = $this->db->execute($query, $couponStatusName);
			if ($result === false) {
				return false;
			}
			return $result->fields['idx'];
		}
        
        /** 
         * 쿠폰 유저 테이블에 삽입 
         *
         * @param  array $param 쿠폰 지급 테이블에 삽입할 폼 데이터
         *
         * @return int/bool
         */
		public function insertCouponMember($param)
		{
			$query = 'INSERT INTO `imi_coupon_member` SET 
						`issue_type` = ?,
						`coupon_idx` = ?,
						`sell_item_idx` = ?,
						`member_idx` = ?,
						`subject` = ?,
						`discount_rate` = ?,
						`item_money` = ?,
						`coupon_status` = ?';
			
			$result = $this->db->execute($query,$param);
			$inserId = $this->db->insert_id();
			if ($inserId < 1) {
				return false;
			}
			return $inserId;
		}
        
        /**
         * 쿠폰 지급 테이블에서 쿠폰 키 가져오기  (유효성 검증)
         *
         * @param array $param  쿠폰 지급테이블을 검색하기 위한 데이터 
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return int/bool
         */
		public function getCheckCouponMemberIdx($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `coupon_idx` 
					  FROM `imi_coupon_member` 
					  WHERE `idx` = ?
					  AND `is_del` = ?';
            
            if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
            
			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}
			return $result->fields['coupon_idx'];
		}
        
        /**
         * 사용자에게 지급된 쿠폰과 사용내역을 함께 조회
         *
         * @param array $param
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getCouponProvierStatus($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `icm`.`issue_type`,
							 `ic`.`subject`,
							 `ic`.`discount_rate`,
							 `ic`.`discount_mileage`,
							 `ic`.`item_money`,
							 `icm`.`idx`,
							 `isi`.`item_name`,
							 `icu`.`idx` `use_idx`,
							 `icu`.`coupon_use_mileage`,
							 `icm`.`is_del`,
							 `ic`.`idx` `coupon_idx`
					  FROM `imi_coupon_member` `icm`
						INNER JOIN `imi_sell_item` `isi`
							ON `icm`.`sell_item_idx` = `isi`.`idx`
						INNER JOIN `imi_coupon` `ic`
							ON `icm`.`coupon_idx` = `ic`.`idx`
						LEFT JOIN `imi_coupon_useage` `icu`
							ON `icm`.`idx` = `icu`.`coupon_member_idx`
							AND `icu`.`is_refund` = ?
					  WHERE `icm`.`member_idx` = ?
					  AND `icm`.`is_del` = ?
					  AND `icm`.`is_coupon_del` = ?
					  ORDER BY `ic`.`start_date` DESC, `icm`.`idx` ASC';
            
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
         * 쿠폰 지급 테이블의 고유 키 값 가져오기
         *
         * @param array $param
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return int/booelan
         */
		public function getCouponMemberIdx($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `idx`
					  FROM `imi_coupon_member` 
					  WHERE `idx` = ? 
					  AND is_del = ?';
            
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
         * 지급된 쿠폰을 삭제 
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function deleteCouponMember($param)
		{
			$query = 'UPDATE `imi_coupon_member` SET 
						`is_del` = ?
					   WHERE `idx` = ?';
			$result = $this->db->execute($query, $param);
			$affected_row = $this->db->affected_rows();
			if ($affected_row < 1) {
				return false;
			}
			return $affected_row;
		}
        
        /** 
         * 지급된 쿠폰이 사용중인지 체크 
         *
         * @param array $param
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return int/bool
         */
		public function getCheckIsUseCouponByMember($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `idx` 
					  FROM `imi_coupon_useage`
					  WHERE `coupon_member_idx` = ?
					  AND `coupon_idx` = ?
					  AND `is_refund` = ?';
            
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
         * 사용가능한 쿠폰 리스트 (기간이 지날 경우 보지이지 않아야 함)
         *
         * @param array $param
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getMemberUseAvailableCouponList($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `icm`.`issue_type`,
							 `ic`.`subject`,
							 `ic`.`discount_rate`,
							 `ic`.`discount_mileage`,
							 `ic`.`item_money`,
							 `ic`.`start_date`,
							 `ic`.`expiration_date`,
							 `icm`.`idx`,
							 `icm`.`coupon_status`,
							 `isi`.`item_name`
					  FROM `imi_coupon_member` `icm`
						INNER JOIN `imi_sell_item` `isi`
							ON `icm`.`sell_item_idx` = `isi`.`idx`
						INNER JOIN `imi_coupon` `ic`
							ON `icm`.`coupon_idx` = `ic`.`idx`
					  WHERE `icm`.`is_coupon_del` = ?
					  AND `icm`.`is_del` = ?
					  AND `icm`.`member_idx` = ?';
            
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
         * 쿠폰 발행 시 이중등록 방지
         *
         * @param array $param 
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return int/bool
         */
		public function getCheckCouponOverlapData($param, $isUseForUpdate = false)
		{
			$query = 'SELECT count(`idx`) `count`
					  FROM `imi_coupon_member`
					  WHERE `coupon_idx` = ?
					  AND `member_idx` = ?
					  AND `issue_type` = ?
					  AND `is_coupon_del` = ?
					  AND `is_del` = ?';
            
            if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}
			return $result->fields['count'];
		}
        
        /**
         * 지급된 쿠폰의 삭제 여부 가져오기
         *
         * @param int $idx
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @rturn string/bool
         */
		public function getCheckCouponMemeberDelete($idx, $isUseForUpdate = false)
		{
			$query = 'SELECT `is_del` FROM `imi_coupon_member` WHERE `idx` = ?';
            
            if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
            
			$result = $this->db->execute($query, $idx);
			if ($result === false) {
				return false;
			}
			return $result->fields['is_del'];
		}
        
        /**
         * 지급된 쿠폰을 변경하기전에 다른 유저에 의해 등록됬는지 확인 
         *
         * @param array $param
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return int/bool
         */
		public function getExistCouponMemberIdx($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `coupon_idx`
					  FROM `imi_coupon_member`
					  WHERE `coupon_idx` = ?
					  AND `member_idx` = ?
					  AND `is_del` = ? ';
            
            if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
            
			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}
			return $result->fields['coupon_idx'];
		}
        
        /**
         * 쿠폰 지급 정보를 수정 
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function updateCouponMember($param)
		{
			$query = 'UPDATE `imi_coupon_member` SET 
					  `issue_type` = ?,
					  `coupon_idx` = ?,
					  `sell_item_idx` = ?,
					  `subject` = ?,
					  `discount_rate` = ?,
					  `item_money` = ?,
					  `is_del` = ?
					  WHERE `idx` = ?';
			
			$result = $this->db->execute($query, $param);
			$affected_row = $this->db->affected_rows();
			if ($affected_row < 1) {
				return false;
			}
			return $affected_row;
		}
        
        /**
         * 쿠폰 지급 정보에 상태를 수정
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function updateCouponMemberStatus($param)
		{
			$query = 'UPDATE `imi_coupon_member` SET 
						`coupon_status` = ?
						WHERE `idx` = ?';
			
			$result = $this->db->execute($query, $param);
			$affected_row = $this->db->affected_rows();
			if ($affected_row < 1) {
				return false;
			}
			return $affected_row;
		}

		/**
		 * 쿠폰 사용 정보 출력
		 *
		 * @param array $param  쿠폰 사용 내역 조회에 필요한 정보
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
		 *
		 * @return array/bool
		 */
		public function getUseCouponData($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `ic`.`subject`,
							 `ic`.`discount_rate`,
							 `ic`.`item_money`,
							 `icu`.`idx`,
							 `icu`.`coupon_member_idx`,
							 `icu`.`coupon_use_before_mileage`,
							 `icu`.`coupon_use_mileage`,
							 ROUND((`ic`.`item_money` * `ic`.`discount_rate`)/100) `discount_money`
					  FROM `imi_coupon_useage` `icu`
						LEFT JOIN `imi_coupon` `ic`
							ON `icu`.`coupon_idx` = `ic`.`idx`
					  WHERE `icu`.`dealings_idx` = ?
					  AND `icu`.`member_idx` = ?
					  AND `icu`.`issue_type` = ?
					  AND `icu`.`is_refund` = ?
					  FOR UPDATE';

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
		 * 거래 생성 시 쿠폰 상태 변경
		 *
		 * @param array $param  쿠폰 상태 변경시 필요한 정보를 배열로 전달
		 *
		 * @return array 
		 */
		public function couponStatusProcess($param)
		{
			$cUseageP = $param['useageP'];

			$cUseageQ = 'INSERT INTO `imi_coupon_useage` SET 
							`issue_type` = ?,
							`dealings_idx` = ?,
							`coupon_idx` = ?,
							`member_idx` = ?,
							`coupon_use_before_mileage` = ?,
							`coupon_use_mileage` = ?,
							`coupon_use_start_date` = CURDATE(),
							`coupon_member_idx` = ?';
			
			$cUseageResult = $this->db->execute($cUseageQ, $cUseageP);
			
			$useageInserId = $this->db->insert_id();
			if ($useageInserId < 1) {
				return [
					'result'=> false,
					'resultMessage'=> '쿠폰사용내역을 입력하는 중에 오류가 발생했습니다.'
				];
			}

			$couponMbStParam = [
				'coupon_status'=> $param['coupon_status_code'],
				'idx'=> $param['useageP']['coupon_member_idx']
			];

			$uCouponMbQ = 'UPDATE `imi_coupon_member` SET 
							`coupon_status` = ?
							WHERE `idx` = ?';
			
			$uCouponMbResult = $this->db->execute($uCouponMbQ, $couponMbStParam);

			$couponMbAffectedRow = $this->db->affected_rows();
			if ($couponMbAffectedRow < 1) {
				return [
					'result'=> false,
					'resultMessage'=> '쿠폰 지급 정보 상태를 변경하는 중에 오류가 발생했습니다.'
				];
			}

			return [
				'result'=> true,
				'resultMessage'=> ''
			];
		}

		/**
		 * 거래 취소 시 쿠폰 복구 
		 *
		 * @param array $param  쿠폰 복구 시 필요한 정보를 배열로 전달
		 *
		 * @return array
		 */
		public function couponRefundProcess($param)
		{
			$uCouponUseP = [
				'coupon_use_end_date'=> $param['coupon_use_end_date'],
				'is_refund'=> $param['is_refund'],
				'idx'=> $param['idx']
			];

			$uCoponUseQ = 'UPDATE `imi_coupon_useage` SET 
							`coupon_use_end_date` = ?,
							`is_refund` = ?
							WHERE `idx` = ?';
			
			$couponUseResult = $this->db->execute($uCoponUseQ, $uCouponUseP);

			$couponUseAffectedRow = $this->db->affected_rows();
			if ($couponUseAffectedRow < 1) {
				return [
					'result'=> false,
					'resultMessage'=> '쿠폰 사용내역을 수정하면서 오류가 발생했습니다.'
				];
			}

			$couponStatusName = '사용대기';
			
			$cCouponCodeQ = 'SELECT `idx`  FROM `imi_coupon_status_code` WHERE `coupon_status_name` = ?';
            
			$cCouponCodeResult = $this->db->execute($cCouponCodeQ, $couponStatusName);
			if ($cCouponCodeResult === false) {
				return [
					'result'=> false,
					'resultMessage'=> '쿠폰 상태 코드를 조회하면서 오류가 발생했습니다.'
				];
			}

			$couponStatusCode = $cCouponCodeResult->fields['idx'];
			if (empty($couponStatusCode)) {
				return [
					'result'=> false,
					'resultMessage'=> '쿠폰 상태 코드를 찾을 수 없습니다.'
				];
			}

			$uCouponMemberP = [
				'coupon_status'=> $couponStatusCode,
				'idx'=> $param['coupon_member_idx']
			];
			$uCouponMemberQ = 'UPDATE `imi_coupon_member` SET 
								`coupon_status` = ?
								WHERE `idx` = ?';
			
			$uCouponMemberResult = $this->db->execute($uCouponMemberQ, $uCouponMemberP);
			
			$uCouponMemberAffectedRow = $this->db->affected_rows();
			if ($uCouponMemberAffectedRow < 1) {
				return [
					'result'=> false,
					'resultMessage'=> '쿠폰 고객 정보를 수정하면서 오류가 발생했습니다.'
				];
			}

			return [
				'result'=> true,
				'resultMessage'=> ''
			];
		}
	}