<?php
	/**
	 * @file CouponClass.php
	 * @brief 쿠폰 클래스, 쿠폰에 대한 기능 서술
	 * @author 이태희
	 */
	Class CouponClass 
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
		 * @brief: 유효성 검증(상품권 등록)
		 * @param: 폼 데이터
		 * @return: true/false, error-message 배열
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
		 * @brief: 상품권 관련 값 체크 
		 * @param: 폼 데이터
		 * @return: true/false, error-message 배열
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
		 * @brief: 쿠폰 생성
		 * @param: 폼에서 넘긴 쿠폰발행타입, 쿠폰제목, 금액을 담은  array
		 * @return: int
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
		 * @brief: 이용 가능한 쿠폰 조회
		 * @param: 거래되는 제품, 타입(구매/판매)을 담은 array
		 * @return: array
		 */
		public function getAvailableCouponData($param)
		{

				$query  = 'SELECT `ic`.`idx`,
							  `ic`.`subject`,
							  `ic`.`item_money`,
							  `ic`.`discount_rate`,
							  `ic`.`discount_mileage`
					   FROM `imi_coupon` `ic` 
					   WHERE `ic`.`sell_item_idx` IN (?,5) 
                       AND `ic`.`issue_type` = ?
                       AND `ic`.`item_money` IN (?,0)
                       AND `ic`.`is_del` = ?
                       AND `ic`.`start_date` <= ?
					   AND `ic`.`expiration_date` >= ?
                       AND NOT EXISTS (
						SELECT `coupon_idx`
						FROM `imi_coupon_useage` `icu`
						WHERE `icu`.`coupon_idx` = `ic`.`idx`
                        AND `icu`.`member_idx` = ?
						AND `icu`.`is_refund` = ?
						AND `icu`.`coupon_use_start_date` IS NOT NULL
					)';
	
			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result;
		}

		/**
		 * @brief: 이용 가능한 모든 쿠폰 조회
		 * @param: 거래되는 제품, 타입(구매/판매)을 담은 array
		 * @return: array
		 */
		public function getAvailableAllCouponData($param)
		{	
			// 첫번째 쿼리는 고객이 연결되어있지 않은 쿠폰내역을 보여주도록 쿼리 (잘못된 설계)
			// 두번째 쿼리가 쿠폰이 고객에게 지급되었을 때만 보여주도록 하는 쿼리

			/*
            $query  = 'SELECT `ic`.`idx`,
							  `ic`.`subject`,
							  `ic`.`item_money`,
							  `ic`.`discount_rate`,
							  `ic`.`discount_mileage`
					   FROM `imi_coupon` `ic` 
					   WHERE `ic`.`issue_type` = ?
                       AND `ic`.`is_del` = ?
                       AND `ic`.`start_date` <= ?
					   AND `ic`.`expiration_date` >= ?
                       AND NOT EXISTS (
						SELECT `coupon_idx`
						FROM `imi_coupon_useage` `icu`
						WHERE `icu`.`coupon_idx` = `ic`.`idx`
                        AND `icu`.`member_idx` = ?
						AND `icu`.`is_refund` = ?
						AND `icu`.`coupon_use_start_date` IS NOT NULL
					)';
			*/

			$query  = 'SELECT `icm`.`idx`,
							  `icm`.`subject`,
							  `icm`.`item_money`,
							  `icm`.`discount_rate`
					   FROM `imi_coupon_member` `icm` 
					   WHERE `icm`.`issue_type` = ?
                       AND `icm`.`is_coupon_del` = ?
					   AND `icm`.`is_del` = ?
					   AND NOT EXISTS (
						SELECT `coupon_idx`
						FROM `imi_coupon_useage` `icu`
						WHERE `icm`.`coupon_idx` = `icu`.`coupon_idx`
                        AND `icu`.`member_idx` = ?
						AND `icu`.`is_refund` = ?
						AND `icu`.`coupon_use_start_date` IS NOT NULL
					)';
			
			$result = $this->db->execute($query, $param);
            
			if ($result === false) {
				return false;
			}

			return $result;
		}
		
		/**
		 * @brief: 쿠폰의 키 값이 실제로 사용가능한지 확인 
		 * @param: 쿠폰 키 값, 타입(구매/판매)을 담은 array
		 * @return: array
		 */
		public function getCheckValidCoupon($param)
		{	
			$query = 'SELECT `idx`
					  FROM `imi_coupon`
					  WHERE `issue_type` = ?
					  AND `idx` = ?
					  AND `is_del` = ?
					  FOR UPDATE';

			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result->fields['idx'];
		}

		/**
		 * @brief: 해당 쿠폰을 사용 했는지 체크 
		 * @param: 쿠폰의 키 값 
		 * @return: int 
		 */
		public function getCheckAvailableCoupon($param)
		{
			$query  = 'SELECT `icu`.`member_idx`
					   FROM `imi_coupon` `ic`
							LEFT JOIN `imi_coupon_useage` `icu`
								ON `ic`.idx = `icu`.`coupon_idx`
					   WHERE `ic`.`idx` = ? 
					   AND `ic`.`is_del` = ?
					   AND `icu`.`member_idx` = ?
					   AND `icu`.`is_refund` = ?
					   AND `icu`.`coupon_use_end_date` is not null
					   FOR UPDATE';
			
			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result->fields['member_idx'];
		}

		/**
		 * @brief: 쿠폰을 적용 받았을 때 할인되는 금액 가져오기 
		 * @param: 쿠폰의 키 값 
		 * @return: int 
		 */
		public function getDiscountMileage($couponIdx)
		{
			$query = 'SELECT `discount_mileage` FROM `imi_coupon` WHERE `idx` = ? FOR UPDATE';

			$result = $this->db->execute($query, $couponIdx);
			if ($result === false) {
				return false;
			}

			return $result->fields['discount_mileage'];
		}

		/**
		 * @brief: 쿠폰 할인율 가져오기
		 * @param: 쿠폰의 키 값 
		 * @return: int 
		 */
		public function getDiscountRate($couponIdx)
		{
			$query = 'SELECT `discount_rate` FROM `imi_coupon` WHERE `idx` = ? FOR UPDATE';

			$result = $this->db->execute($query, $couponIdx);
			if ($result === false) {
				return false;
			}

			return $result->fields['discount_rate'];
		}

		/**
		 * @brief: 쿠폰 가격 가져오기
		 * @param: 쿠폰의 키 값 
		 * @return: int 
		 */
		public function getItemMoney($couponIdx)
		{
			$query = 'SELECT `item_money` FROM `imi_coupon` WHERE `idx` = ? FOR UPDATE';

			$result = $this->db->execute($query, $couponIdx);
			if ($result === false) {
				return false;
			}

			return $result->fields['item_money'];
		}

		/**
		 * @brief: 쿠폰 사용 내역 추가
		 * @param: 쿠폰 사용 내역 시 필요한 항목을 array로 받음
		 * @return: int 
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
			
			$result = $this->db->execute($query,$param);
			$inserId = $this->db->insert_id();

			if ($inserId < 1) {
				return false;
			}
			return $inserId;
		}

		/**
		 * @brief: 쿠폰 사용 정보 출력 
		 * @param: 회원PK, 발행타입, 거래PK을 array로 받음
		 * @return: int 
		 */
		public function getUseCouponData($param)
		{
			$query = 'SELECT `ic`.`subject`,
							 `ic`.`discount_rate`,
							 `ic`.`item_money`,
							 `icu`.`idx`,
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

			$result = $this->db->execute($query, $param);

			if ($result === false) {
				return false;
			}

			return $result;
		}

		/**
		 * @brief: 관리자가 생성한 쿠폰정보 변경하기 
		 * @param: 쿠폰 키값과 완료일자, 환불일자을 담은 배열 
		 * @return: int 
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
		 * @brief: 사용자 쿠폰 사용 내역 조회 기능 
		 * @param: none
		 * @return: array 
		 */
		public function getCouponUseList()
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
					  ORDER BY `icu`.`member_idx` ASC, `ic`.`subject` ASC, `icu`.`coupon_use_end_date` DESC';
			
			$result = $this->db->execute($query);
			if ($result === false) {
				return false;
			}

			return $result;
		}

		/**
		 * @brief: 사용자 쿠폰 발행 내역 조회 기능 
		 * @param: none
		 * @return: array 
		 */
		public function getCouponIssueList()
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
			
			$result = $this->db->execute($query);
			if ($result === false) {
				return false;
			}

			return $result;
		}

		/**
		 * @brief: 유효기간이 지난 데이터가 얼마나 있는지 카운트(크론탭 사용)
		 * @param: 삭제여부, 유효기간을 담은 array 
		 * @return: int 
		 */
		public function getCheckCouponValidDateCount($param)
		{
			$query = 'SELECT count(`idx`) `cnt` 
					  FROM `imi_coupon` 
					  WHERE `expiration_date` < ?  
					  AND `is_del` <> ? 
					  FOR UPDATE
					';

			$result = $this->db->execute($query, $param);

			if ($result === false) {
				return false;
			}

			return $result->fields['cnt'];
		}

		/**
		 * @brief: 사용자에게 지급된 쿠폰 중 유효기간이 지났으면서, 사용하지 않은 쿠폰에 대해 삭제처리 (크론탭 사용)
		 * @param: 삭제여부, 유효기간을 담은 array 
		 * @return: int 
		 */
		public function getCheckCouponValidDateList($param)
		{
			$query = 'SELECT `ic`.`idx`
					  FROM `imi_coupon` `ic`
						LEFT JOIN `imi_coupon_member` `icm`
							ON `ic`.`idx` = `icm`.`coupon_idx`
					  WHERE `ic`.`expiration_date` < ?  
					  AND `ic`.`is_del` = ?
					  AND `icm`.`idx` IS NOT NULL
					  FOR UPDATE';

			$result = $this->db->execute($query, $param);

			if ($result === false) {
				return false;
			}

			return $result;
		}

		/**
		 * @brief: 쿠폰이 종료된 경우 삭제 처리 (크론탭 사용)
		 * @param: 오늘날짜
		 * @return: int 
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
		 * @brief: 고객에게 지급된 쿠폰 중 유효기간이 지나고, 사용내역이 없는 건 삭제 (크론탭 사용)
		 * @param: 쿠폰의 삭제여부와 쿠폰 키 값을 담은 array
		 * @return: int 
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
		 * @brief: 사용자가 선택한 쿠폰이 실제로 있는 쿠폰인지 확인
		 * @param: 상품권 번호와 가격을 담은 array 
		 * @return: int 
		 */
		public function getCheckUserCouponMatch($param)
		{
			$query = 'SELECT `idx` FROM `imi_coupon` WHERE `item_money` = ? AND `sell_item_idx` = ?';

			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}

			return $result->fields['idx'];
		}

		/**
		 * @brief: 회원에 발행 가능한 쿠폰 리스트 보여주기 
		 * @param: 회원 키
		 * @return: int 
		 */
		public function getMemberAvailableCouponList($param)
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

			$result = $this->db->execute($query, $param);

			if ($result === false) {
				return false;
			}

			return $result;
		}

		public function getMemberCouponData($memberIdx)
		{
			$query = 'SELECT `ic`.`idx`,
							 `ic`.`issue_type`,
							 `ic`.`subject`,
							 `ic`.`item_money`,
							 `ic`.`discount_rate`,
							 `ic`.`discount_mileage`,
							 `ic`.`sell_item_idx`,
							 `isi`.`item_name`
					  FROM `imi_coupon` `ic`
						LEFT JOIN `imi_sell_item` `isi`
							ON `ic`.`sell_item_idx` = `isi`.`idx`
				      WHERE `ic`.`idx`= ?';

			$result = $this->db->execute($query, $memberIdx);

			if ($result === false) {
				return false;
			}

			return $result;
		}

		/**
		 * @brief: 쿠폰의 키가 유효한지 확인 
		 * @param: 쿠폰 키
		 * @return: int 
		 */
		public function getValidCouponIdx($couponIdx)
		{
			$query = 'SELECT `idx` FROM `imi_coupon` WHERE `idx` = ?';

			$result = $this->db->execute($query, $couponIdx);

			if ($result === false) {
				return false;
			}

			return $result->fields['idx'];
		}

		/**
		 * @brief: 쿠폰 유저 테이블에 삽입하기 
		 * @param: 쿠폰 유저 데이터
		 * @return: int 
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
						`item_money` = ?';
			
			$result = $this->db->execute($query,$param);
			$inserId = $this->db->insert_id();

			if ($inserId < 1) {
				return false;
			}
			return $inserId;
		}

		/**
		 * @brief: 쿠폰 키 값 가져오기 (유효성 검증)
		 * @param: 쿠폰 유저 데이터
		 * @return: int 
		 */
		public function getCheckCouponMemberIdx($param)
		{
			$query = 'SELECT `coupon_idx` FROM `imi_coupon_member` WHERE `idx` = ? AND is_del = ?';

			$result = $this->db->execute($query, $param);

			if ($result === false) {
				return false;
			}

			return $result->fields['coupon_idx'];
		}

		/**
		 * @brief: 사용자에게 지급된 쿠폰 조회 
		 * @param: 사용자 키 값
		 * @return: array 
		 */
		public function getCouponProvierStatus($param)
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
							 `icm`.`is_del`
					  FROM `imi_coupon_member` `icm`
						INNER JOIN `imi_sell_item` `isi`
							ON `icm`.`sell_item_idx` = `isi`.`idx`
						INNER JOIN `imi_coupon` `ic`
							ON `icm`.`coupon_idx` = `ic`.`idx`
						LEFT JOIN `imi_coupon_useage` `icu`
							ON `icm`.`idx` = `icu`.`coupon_member_idx`
							AND `icu`.`is_refund` = ?
					  WHERE `icm`.`member_idx` = ?';

			$result = $this->db->execute($query, $param);

			if ($result === false) {
				return false;
			}

			return $result;
		}

		/**
		 * @brief: `imi_coupon_member`고유 키 값 가져와서 검증 
		 * @param:  회원키와 삭제여부를 담은 array
		 * @return: int 
		 */
		public function getCouponMemberIdx($param)
		{
			$query = 'SELECT `idx` FROM `imi_coupon_member` WHERE `idx` = ? AND is_del = ?';

			$result = $this->db->execute($query, $param);

			if ($result === false) {
				return false;
			}

			return $result->fields['idx'];
		}

		/**
		 * @brief: 지급된 쿠폰을 삭제
		 * @param: 지급된 쿠폰의 고유키와  삭제여부를 담은 array
		 * @return: int 
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
		 * @brief: 지급된 쿠폰이미 사용 중인지 체크
		 * @param: 지급된 쿠폰의 고유 키
		 * @return: int 
		 */
		public function getCheckIsUseCouponByMember($couponMemberIdx)
		{
			$query = 'SELECT `idx` FROM `imi_coupon_useage` WHERE `coupon_idx` = ?';

			$result = $this->db->execute($query, $couponMemberIdx);

			if ($result === false) {
				return false;
			}

			return $result->fields['idx'];
		}

		/**
		 * @brief: 사용 가능한 쿠폰 리스트 (기간 지날 경우 삭제 되어 보이지 않음)
		 * @param: 회원 키, 쿠폰 삭제 을 담은 배열 
		 * @return: array
		 */
		public function getMemberUseAvailableCouponList($param)
		{
			$query = 'SELECT `icm`.`issue_type`,
							 `ic`.`subject`,
							 `ic`.`discount_rate`,
							 `ic`.`discount_mileage`,
							 `ic`.`item_money`,
							 `ic`.`start_date`,
							 `ic`.`expiration_date`,
							 `icm`.`idx`,
							 `isi`.`item_name`,
							 `icu`.`idx` `use_idx`
					  FROM `imi_coupon_member` `icm`
						INNER JOIN `imi_sell_item` `isi`
							ON `icm`.`sell_item_idx` = `isi`.`idx`
						INNER JOIN `imi_coupon` `ic`
							ON `icm`.`coupon_idx` = `ic`.`idx`
						LEFT JOIN `imi_coupon_useage` `icu`
							ON `icm`.`idx` = `icu`.`coupon_member_idx`
							AND `icu`.`is_refund` = ?
					  WHERE `icm`.`is_coupon_del` = ?
					  AND `icm`.`is_del` = ?
					  AND `icm`.`member_idx` = ?';
			
			$result = $this->db->execute($query, $param);

			if ($result === false) {
				return false;
			}

			return $result;
		}

	}