<?php
	/**
	 * 쿠폰 클래스
	 */
	class CouponClass
	{
		/** @var string|null $db 는 데이터베이션 커넥션 객체를 할당하기 전에 초기화 함*/
		private $db = null;

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
         * 쿠폰을 지급(발급)
         *
         * @param array $param 쿠폰 지급(발급)시 필요한 정보를 배열로 전달
         *
         * @return array
         */
        public function couponProvideProcess($param)
        {
            // 쿠폰 고유번호
            $couponIdx = $param['coupon_idx'];

            // 쿠폰 정보 조회
            $rCouponQ = 'SELECT `idx`,
                                `issue_type`,
                                `subject`,
                                `item_money`,
                                `discount_rate`,
                                `discount_mileage`,
                                `sell_item_idx`,
                                `is_del`
                          FROM `th_coupon`
                          WHERE `idx` = ?
                          FOR UPDATE';

            $rCouponResult = $this->db->execute($rCouponQ, $couponIdx);
            if ($rCouponResult === false) {
                return [
                    'result'=> false,
                    'resultMessage'=> '쿠폰 정보를 조회하면서 오류가 발생하였습니다.'
                ];
            }

            // 쿠폰이 이미 지급 되어있는지 확인
            $rCouponOverlapP = [
                'coupon_idx'=> $couponIdx,
                'member_idx'=> $param['member_idx'],
                'is_coupon_del'=> $param['is_coupon_del'],
                'is_del'=> $param['is_del']
            ];

            $rCouponOverlapQ = 'SELECT COUNT(`idx`) `cnt`
					            FROM `th_coupon_member`
					            WHERE `coupon_idx` = ?
					            AND `member_idx` = ?
					            AND `is_coupon_del` = ?
					            AND `is_del` = ?';

            $rCouponOverlapResult = $this->db->execute($rCouponOverlapQ, $rCouponOverlapP);
            if ($rCouponOverlapResult === false) {
                return [
                    'result'=> false,
                    'resultMessage'=> '쿠폰 중복 지급을 체크하면서 오류가 발생했습니다.'
                ];
            }

            $couponOverlapCnt = $rCouponOverlapResult->fields['cnt'];
            if ($couponOverlapCnt > 0) {
                return [
                    'result'=> false,
                    'resultMessage'=> '이미 쿠폰이 지급되었습니다.'
                ];
            }

            // 쿠폰을 지급
            $cCouponMemberP = [
                'issue_type'=> $rCouponResult->fields['issue_type'],
                'coupon_idx'=> $rCouponResult->fields['idx'],
                'sell_item_idx'=> $rCouponResult->fields['sell_item_idx'],
                'member_idx'=> $param['member_idx'],
                'subject'=> $rCouponResult->fields['subject'],
                'discount_rate'=> $rCouponResult->fields['discount_rate'],
                'item_money'=> $rCouponResult->fields['item_money'],
                'coupon_status'=> $param['coupon_status']
            ];

            $cCouponMemberQ = 'INSERT INTO `th_coupon_member` SET 
                                `issue_type` = ?,
                                `coupon_idx` = ?,
                                `sell_item_idx` = ?,
                                `member_idx` = ?,
                                `subject` = ?,
                                `discount_rate` = ?,
                                `item_money` = ?,
                                `coupon_status` = ?';

            $this->db->execute($cCouponMemberQ, $cCouponMemberP);

            $cCouponMemberInsertId = $this->db->insert_id();
            if ($cCouponMemberInsertId < 1) {
                return [
                    'result'=> false,
                    'resultMessage'=> '쿠폰을 지급하는 중에 오류가 발생했습니다.'
                ];
            }

            return [
                'result'=> true,
                'resultMessage'=> ''
            ];
        }

		/**
		 * 거래 시 쿠폰을 적용하여 상태 변경
		 *
		 * @param array $param  쿠폰 상태 변경시 필요한 정보를 배열로 전달
		 *
		 * @return array 
		 */
		public function couponStatusProcess($param)
		{
			$cUseP = $param['useageP'];

			$cUseQ = 'INSERT INTO `th_coupon_useage` SET 
							`issue_type` = ?,
							`dealings_idx` = ?,
							`coupon_idx` = ?,
							`member_idx` = ?,
							`coupon_use_before_mileage` = ?,
							`coupon_use_mileage` = ?,
							`coupon_use_start_date` = CURDATE(),
							`coupon_member_idx` = ?';
			
			$this->db->execute($cUseQ, $cUseP);
			
			$useInsertId = $this->db->insert_id();
			if ($useInsertId < 1) {
				return [
					'result'=> false,
					'resultMessage'=> '쿠폰사용내역을 입력하는 중에 오류가 발생했습니다.'
				];
			}

			$couponMbStParam = [
				'coupon_status'=> $param['coupon_status_code'],
				'idx'=> $param['useageP']['coupon_member_idx']
			];

			$uCouponMbQ = 'UPDATE `th_coupon_member` SET 
							`coupon_status` = ?
							WHERE `idx` = ?';
			$this->db->execute($uCouponMbQ, $couponMbStParam);

			$couponMbAffectedRow = $this->db->affected_rows();
			if ($couponMbAffectedRow < 0) {
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
			// 쿠폰 사용내역 파라미터
			$couponUseP = [
				'dealings_idx'=> $param['dealings_idx'],
				'member_idx'=> $param['member_idx'],
				'issue_type'=> $param['issue_type'],
				'is_refund'=> $param['is_refund']
			];

			// 쿠폰 사용내역 및 쿠폰정보 가져오기
			$rCouponUseQ = 'SELECT * 
                            FROM `th_coupon_useage`
                            WHERE `dealings_idx` = ?
                            AND `member_idx` = ?
                            AND `issue_type` = ?
                            AND `is_refund` = ?
                            FOR UPDATE';

			$rCouponUseResult = $this->db->execute($rCouponUseQ, $couponUseP);
			if ($rCouponUseResult === false) {
				return [
					'result'=> false,
					'resultMessage'=> '쿠폰 사용내역을 조회 하면서 오류가 발생하였습니다.'
				];
			}

			$couponIdx = $rCouponUseResult->fields['idx'];
			$couponMemberIdx = $rCouponUseResult->fields['coupon_member_idx'];
			$couponIsRefund = $rCouponUseResult->fields['is_refund'];
			$couponUseMileage = $rCouponUseResult->fields['coupon_use_mileage'];

			if (empty($couponIdx)) {
				// 쿠폰 사용내역이 없으면 진행하지 않음
				return [
					'result'=> true,
					'resultMessage'=> ''
				];
			}

			if ($couponIsRefund == 'Y') {
				return [
					'result'=> false,
					'resultMessage'=> '쿠폰이 이미 환불 되었습니다.'
				];
			}

			$uCouponUseP = [
				'coupon_use_end_date'=> $param['coupon_use_end_date'],
				'is_refund'=> 'Y',
				'idx'=> $couponIdx
			];

			$uCouponUseQ = 'UPDATE `th_coupon_useage` SET 
							`coupon_use_end_date` = ?,
							`is_refund` = ?
							WHERE `idx` = ?';
			
			$this->db->execute($uCouponUseQ, $uCouponUseP);

			$couponUseAffectedRow = $this->db->affected_rows();
			if ($couponUseAffectedRow < 0) {
				return [
					'result'=> false,
					'resultMessage'=> '쿠폰 사용내역을 수정하면서 오류가 발생했습니다.'
				];
			}

			$couponStatusName = '사용대기';
			
			$cCouponCodeQ = 'SELECT `idx`  FROM `th_coupon_status_code` WHERE `coupon_status_name` = ?';
            
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
				'idx'=> $couponMemberIdx
			];
			$uCouponMemberQ = 'UPDATE `th_coupon_member` SET 
								`coupon_status` = ?
								WHERE `idx` = ?';
			
			$this->db->execute($uCouponMemberQ, $uCouponMemberP);
			
			$uCouponMemberAffectedRow = $this->db->affected_rows();
			if ($uCouponMemberAffectedRow < 0) {
				return [
					'result'=> false,
					'resultMessage'=> '쿠폰 고객 정보를 수정하면서 오류가 발생했습니다.'
				];
			}

			return [
				'result'=> true,
				'resultMessage'=> '',
				'data'=> [
					'couponIdx'=> $couponIdx,
					'couponUseMileage'=> $couponUseMileage
				]
			];
		}
	}