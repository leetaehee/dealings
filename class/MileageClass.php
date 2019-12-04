<?php
    /**
	 * 마일리지 클래스 
	 */
	class MileageClass
	{
		/** @var string|null $db 는 데이터베이션 커넥션 객체를 할당하기 전에 초기화 함*/
		private $db = null;
		
		/**
         * 마일리지 타입별 합계 테이블의 컬럼명 리턴
         *
         * @param string $mileageTypeCode
         *
         * @return string
         */
		private function getMileageTypeColumn($mileageTypeCode){
			switch($mileageTypeCode){
				case 1:
					$colName = 'card_sum';
					break;
				case 2:
					$colName = 'phone_sum';
					break;
				case 3:
					$colName = 'culcture_voucher_sum';
					break;
				case 5:
					$colName = 'virtual_account_sum';
					break;
				case 7:
					$colName = 'dealings_sum';
					break;
				case 8:
					$colName = 'event_sum';
					break;
			}
			return $colName;
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
         * 마일리지 충전 폼 유효성 검증
         *
         * @param array $postData
         *
         * @return array
         */
		public function checkChargeFormValidate($postData)
		{	
			if (isset($postData['account_bank']) && empty($postData['account_bank'])) {
				return ['isValid'=>false, 'errorMessage'=>'입금은행을 입력하세요'];
			}

			if (isset($postData['account_no']) && empty($postData['account_no'])) {
				return ['isValid'=>false, 'errorMessage'=>'계좌번호를 입력하세요'];
			}

			if (isset($postData['charge_cost']) && empty($postData['charge_cost'])) {
				return ['isValid'=>false, 'errorMessage'=>'금액을 입력하세요'];
			}

			if (isset($postData['charge_name']) && empty($postData['charge_name'])) {
				return ['isValid'=>false, 'errorMessage'=>'입금자를 입력하세요.'];
			}

			return ['isValid'=>true, 'errorMessage'=>''];
		}

		/**
		 * 마일리지 차감 (결제/출금) 
		 *
		 * @param array @param 결제 시 차감 되는 마일리지 정보  
		 *
		 * @return array 
		 */
		public function withdrawalMileageProcess($param)
		{
			// 결제 가능한 마일리지 리스트
			$mileageWithdrawalList = $param['mileageWithdrawalList'];
			// 결제 해야 하는 마일리지 
			$dealingsMileage = $param['dealingsMileage'];
			// 구분 (거래인지, 마일리지 출금인지)
			$type = $param['type'];
			// 유저정보
			$memberIdx = $param['member_idx'];
			
			$withdrawalListCount = $mileageWithdrawalList->recordCount();
			$remainCost = $dealingsMileage;

			if ($withdrawalListCount > 0) {
				// 충전 된 마일리지가 있는 경우 결제(거래)하는 금액만큼 리스트 추출
				foreach ($mileageWithdrawalList as $key => $value) {
					$useCost = 0;
					$tmpCost = $value['spare_cost'] - $remainCost;

					if ($tmpCost < 0) {
						$useCost = $value['spare_cost'];
						$remainCost -= $value['spare_cost'];
					} else {
						$useCost += $remainCost;
						$remainCost -= $remainCost;
					}

					$mileageChargeData[] = [
						'use_cost'=> $useCost,
						'idx'=> $value['idx'],
						'mileage_idx'=> $value['mileage_idx']
					];

					if ($useCost==0) {
						array_pop($mileageChargeData);
					}

					if ($tmpCost > 0) {
						break;
					}	
				}
			} else {
				return [
					'result'=> false,
					'resultMessage'=> '출금 가능한 마일리지가 존재하지 않습니다.'
				];
			}

			// 충전된 마일리지에서 결제하는 금액만큼 사용처리
			for ($i = 0; $i < count($mileageChargeData); $i++) {
				$uChargeP = [
					'spare_cost'=> $mileageChargeData[$i]['use_cost'],
					'use_cost'=> $mileageChargeData[$i]['use_cost'],
					'idx'=> $mileageChargeData[$i]['idx'] 
				];

				// 충전된 마일리지에서 차감
				$uChargeQ = 'UPDATE `th_mileage_charge` SET
									 `spare_cost` = `spare_cost` - ?,
									 `use_cost` = `use_cost` + ?
									WHERE `idx` = ?';
					
				$this->db->execute($uChargeQ, $uChargeP);

				$chargeAffectedRow = $this->db->affected_rows();
				if ($chargeAffectedRow < 0) {
					return [
						'result'=> false,
						'resultMessage'=> '충전금액을 사용 처리 하면서 오류가 발생했습니다.'
					];
				}

				$mileageIdx = $mileageChargeData[$i]['mileage_idx'];

				// 마일리지 유형별로 차감 
				$colName = $this->getMileageTypeColumn($mileageIdx);

				$uMileageSumP = [
					'use_cost'=> $mileageChargeData[$i]['use_cost'],
					'member_idx'=> $memberIdx
				];

				$uMileageSumQ = "UPDATE `th_mileage_type_sum` SET
								`{$colName}` = `{$colName}` - ?
								WHERE `member_idx` = ?";

				$this->db->execute($uMileageSumQ, $uMileageSumP);
	
				$mileageSumAffectedRow = $this->db->affected_rows();
				if ($mileageSumAffectedRow < 0) {
					return [
						'result'=> false,
						'resultMessage'=> '마일리지 유형별 출금금액을 수정하면서 오류가 발생했습니다.'
					];
				}
			}

			// 사용처리 한 금액에서 모두 사용하였지만 상태가 '사용완료'가 아닌 항목 카운트
			$rUseChargeQ = 'SELECT COUNT(`spare_cost`) spare_cost
							FROM `th_mileage_charge`
							WHERE `spare_cost` < 1
							AND `charge_status` <> 6';
			
			$rUseChargeResult = $this->db->execute($rUseChargeQ);
			if ($rUseChargeResult === false) {
				return [
					'result'=> false,
					'resultMessage'=> '사용완료 된 금액을 조회 하면서 오류가 발생했습니다.'
				];
			}

			// 사용된 금액이 0이면서 상태가 변경되지 않은 항목 카운트
			$spareZeroCount = $rUseChargeResult->fields['spare_cost'];
			if ($spareZeroCount > 0) {
				$uChargeStatusQ = 'UPDATE `th_mileage_charge` SET
									`charge_status` = 6
									WHERE `spare_cost` = 0';
			
				$this->db->execute($uChargeStatusQ);
				
				$chargeStatusAffectedRow = $this->db->affected_rows();
				if ($chargeStatusAffectedRow < 0) {
					return [
						'result'=> false,
						'resultMessage'=> '사용 완료 상태를 변경하면서 오류가 발생했습니다.'
					];
				}
			}

			// 거래 출금내역 생성
			if ($type == 'dealings') {
				// 거래 시 출금내역은 `th_dealings_mileage_change` 삽입

				$dealingsStatsus = $param['dealings_status'];
				$dealingsWriterIdx = $param['dealings_writer_idx'];
				$dealingsMemberIdx = $param['dealings_member_idx'];
				$dealingsIdx = $param['dealings_idx'];

				for ($i = 0; $i<count($mileageChargeData); $i++) {
					// th_dealings_mileage_change 에 입력데이터 생성
					$cChangeP[] = [
						'use_cost'=> $mileageChargeData[$i]['use_cost'],
						'idx'=> $mileageChargeData[$i]['idx'],
						'dealings_idx'=> $dealingsIdx,
						'dealings_writer_idx'=> $dealingsWriterIdx,
						'dealings_member_idx'=> $dealingsMemberIdx,
						'dealings_status_code'=> $dealingsStatsus
					];
				}

				// 거래 마일리지 변동내역 생성
				for ($i = 0; $i < count($cChangeP); $i++) {
					$cChangeQ = 'INSERT INTO `th_dealings_mileage_change` SET
									`dealings_money` = ?,
									`charge_idx` = ?,
									`dealings_idx` = ?,
									`dealings_writer_idx` = ?,
									`dealings_member_idx` = ?,
									`dealings_status_code` = ?,
									`dealings_date` = CURDATE()';

					$this->db->execute($cChangeQ, $cChangeP[$i]);
					
					$changeInsertId = $this->db->insert_id(); // 추가
					if ($changeInsertId < 1) {
						return [
							'result'=> false,
							'resultMessage'=> '거래 출금데이터을 생성하면서 오류가 발생했습니다.'
						];
					}
				}
			}

			// 마일리지 출금내역 생성
			if ($type == 'withdrawal') {
				// 마일리지 출금시 `th_mileage_change`에서 빠져나감
	
				$accountNo = $param['account_no'];
				$accountBank = $param['account_bank'];
				$chargeStatus = $param['charge_status'];
				$chargeName = $param['charge_name'];

				for ($i = 0; $i < count($mileageChargeData); $i++) {
					// th_mileage_change 에 입력데이터 생성
					$cChangeP[] = [
						'charge_idx'=> $mileageChargeData[$i]['idx'],
						'mileage_idx'=> $mileageChargeData[$i]['mileage_idx'],
						'member_idx'=> $memberIdx,
						'charge_account_no'=> $accountNo,
						'charge_infomation'=> $accountBank,
						'charge_name'=> $chargeName,
						'chrage_status'=> $chargeStatus,
						'use_cost'=> $mileageChargeData[$i]['use_cost'],
					];
				}

				// 마일리지 변동내역 생성
				for ($i = 0; $i < count($cChangeP); $i++) {
					$cChangeQ = 'INSERT INTO `th_mileage_change` SET 
									`charge_idx` = ?,
									`mileage_idx` = ?,
									`member_idx` = ?,
									`charge_account_no` = ?,
									`charge_infomation` = ?,
									`charge_name` = ?,
									`charge_status` = ?,
									`charge_cost` = ?,
									`process_date` = CURDATE()';
					
					$this->db->execute($cChangeQ, $cChangeP[$i]);
					
					$changeInsertId = $this->db->insert_id(); // 추가
					if ($changeInsertId < 1) {
						return [
							'result'=> false,
							'resultMessage'=> '마일리지 출금데이터을 생성하면서 오류가 발생했습니다.'
						];
					}
				}
			}

			$uMbMileageP = [
				'mileage'=> $dealingsMileage,
				'idx'=> $memberIdx
			];

			// 회원별 마일리지 수정 
			$uMbMileageQ = 'UPDATE `th_members` SET
							`mileage` = `mileage` - ? 
							WHERE `idx` = ?';

			$this->db->execute($uMbMileageQ, $uMbMileageP);

			$mbMileageAffectedRow = $this->db->affected_rows();
			if ($mbMileageAffectedRow < 0) {
				return [
					'result'=> false,
					'resultMessage'=> '회원 마일리지를 수정하면서 오류가 발생했습니다.'
				];
			}

			return [
				'result'=> true,
				'resultMessage'=> ''
			];
		}

		/**
		 * 마일리지 충전
		 *
		 * @param array $param 마일리지 충전을 위한 데이터를 배열로 받음
		 *
		 * @return array
		 */
		public function chargeMileageProcess($param)
		{
			// 충전자 고유번호
			$memberIdx = $param['charge_param']['member_idx'];

			// 마일리지 금액
			$chargeCost = $param['charge_param']['charge_cost'];

			// 거래 고유번호
			if (isset($param['dealings_idx'])) {
                $dealingsIdx = $param['dealings_idx'] ?? '';
            }

			// 거래 상태번호
			$dealingsStatus = $param['dealings_status'] ?? '';

			// 마일리지 타입
			$mileageType = $param['mileageType'];

			// 만료일자 설정 여부
			if (isset($param['is_set_expiration'])) {
				$isSetExpiration = $param['is_set_expiration'];
			}

			// 충전내역 추가
			$cChargeQ = 'INSERT INTO `th_mileage_charge` SET
							`member_idx` = ?,
							`charge_infomation` = ?,
							`charge_account_no` = ?,
							`charge_cost` = ?,
							`spare_cost` = ?,
							`charge_name` = ?,
							`mileage_idx` = ?,
							`charge_date` = ?,
							`charge_status` = ?';
			
			if(isset($isSetExpiration) && $isSetExpiration == 'Y'){
				/**
				 * 유효기간 정보 추출하여 정보 넣기 (가상계좌의 경우 주기설정이 없어 진행하지않음)
				 */
				$rMileageQ = 'SELECT `expiration_day`,
									 `period` 
							  FROM `th_mileage` 
							  WHERE `idx` = ?
							  FOR UPDATE';

				$rMileageResult = $this->db->execute($rMileageQ, $mileageType);
				if ($rMileageResult === false) {
					return [
						'result'=> false,
						'resultMessage'=> '마일리지 유효기간을 조회하면서 오류가 발생했습니다.'
					];
				}

				$day = $rMileageResult->fields['expiration_day'];
				$period = $rMileageResult->fields['period'];

				// 유효기간 만료일자 지정
				$expirationDate = '';
				if ($period != 'none') {
					// 지역변수
					$today = date('Y-m-d');

					$period = "+".$day.' '.$period;
					$expirationDate = date('Y-m-d', strtotime($period, strtotime($today)));
				}

				// 컬럼추가.
				$cChargeQ .= ', `expiration_date` = ?';

				// 값 설정하여 배열에 추가
				$param['charge_param']['expiration_date'] = $expirationDate;
			}

			$this->db->execute($cChargeQ, $param['charge_param']);

			$chargeInsertId = $this->db->insert_id(); // 추가
			if ($chargeInsertId < 1) {
				return [
					'result'=> false,
					'resultMessage'=> '마일리지 충전하는 중에 오류가 발생하였니다.'
				];
			}

			// 회원 마일리지 변경
			$mileageParam = [
				'mileage'=> $param['charge_param']['charge_cost'],
				'member_idx'=> $memberIdx
			];

			if ($mileageParam['mileage'] > 0) {

				$uMileageQ = 'UPDATE `th_members` SET
							   `mileage` = `mileage` + ? 
							   WHERE `idx` = ?';

				$this->db->execute($uMileageQ, $mileageParam);

				$mileageAffectedRow = $this->db->affected_rows();
				if ($mileageAffectedRow < 0) {
					return [
						'result'=> false,
						'resultMessage'=> '회원 마일리지 변동중에 오류가 발생하였습니다.'
					];
				}

				// 마일리지 유형별 합계 테이블에 데이터 확인
				$rTypeSumQ = 'SELECT `idx`
							  FROM `th_mileage_type_sum` 
							  WHERE `member_idx` = ? 
							  FOR UPDATE';

				$rTypeSumResult = $this->db->execute($rTypeSumQ, $memberIdx);
				if ($rTypeSumResult == false) {
					return [
						'result'=> false,
						'resultMessage'=> '마일리지 유형 테이블을 조회 하는 중에 오류가 발생하였습니다.'
					];
				}

                $colName = $this->getMileageTypeColumn($mileageType);

                $typeSumIdx = $rTypeSumResult->fields['idx'];
				if (!empty($typeSumIdx)){
					// 마일리지 유형별 합계 업데이트
					$mileageTypeParam = [
						'mileage'=> $chargeCost,
						'member_idx'=> $memberIdx
					];

					$uMileageSumQ = "UPDATE `th_mileage_type_sum` SET
										 `{$colName}` = `{$colName}` + ?
										 WHERE `member_idx` = ?";

					$this->db->execute($uMileageSumQ, $mileageTypeParam);
					
					$mileageSumAffectedRows = $this->db->affected_rows();
					if ($mileageSumAffectedRows < 0) {
						return [
							'result'=> false,
							'resultMessage'=> '마일리지 유형별 합계 수정하는데 실패했습니다.' 
						];
					}
				} else {
					// 마일리지 유형별 합계 추가
					$cMileageSumP = [
						'member_idx'=> $memberIdx, 
						'mileage'=> $chargeCost
					];

					$cMileageSumQ = "INSERT INTO `th_mileage_type_sum` SET
										`member_idx` = ?,
										`{$colName}` = `{$colName}` + ?";

					$this->db->execute($cMileageSumQ, $cMileageSumP);

					$mileageSumInsertId = $this->db->insert_id();

					if ($mileageSumInsertId < 1) {
						return [
							'result'=> false,
							'resultMessage'=> '마일리지 유형별 합계 삽입 실패 하였습니다.' 
						];
					}
				}

				if (isset($param['mode']) != 'event') {
					// 거래 마일리지 키 가져오기
					$rDealingsChangeQ = 'SELECT `idx`
										 FROM `th_dealings_mileage_change` 
										 WHERE `dealings_idx` = ?
										 FOR UPDATE';

					$rDealingsChangeResult = $this->db->execute($rDealingsChangeQ, $dealingsIdx);
					if ($rDealingsChangeResult === false) {
						return [
							'result'=> false,
							'resultMessage'=> '거래마일리지 변동내역 조회 시 오류가 발생했습니다.'
						];
					}

					$mileageChangeIdx  = $rDealingsChangeResult->fields['idx'];
					if(!empty($mileageChangeIdx)){
						$changeData = [
							'dealings_status'=> $dealingsStatus,
							'dealings_idx'=> $dealingsIdx
						];

						$uDealingsChangeQ = 'UPDATE `th_dealings_mileage_change` SET 
											  `dealings_status_code` = ? 
											  WHERE `dealings_idx` = ?';
				
						$this->db->execute($uDealingsChangeQ, $changeData);

						$affected_row = $this->db->affected_rows();
						if ($affected_row < 0) {
							return [
								'result'=> false,
								'resultMessage'=> '거래마일리지 변동내역 수정 시 오류가 발생했습니다.'
							];
							
						}
					}
				}
			}

			return [
				'result'=> true
			];
		}
	}
