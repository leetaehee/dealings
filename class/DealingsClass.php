<?php
	/**
     * 거래 클래스 
     */ 
	class DealingsClass
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
         * 거래 글  폼 데이터 유효성 검증
         *
         * @param array $postData
         *
         * @return array
         */
		public function checkDealingFormValidate($postData)
		{
			if (isset($postData['vourcher_state']) && empty($postData['vourcher_state'])) {
				return ['isValid'=>false, 'errorMessage'=>'처리상태를 입력하세요.'];
			}

			if (isset($postData['dealings_type']) && empty($postData['dealings_type'])) {
				return ['isValid'=>false, 'errorMessage'=>'거래종류를 입력하세요.'];
			}

			if (isset($postData['dealings_subject']) && empty($postData['dealings_subject'])) {
				return ['isValid'=>false, 'errorMessage'=>'제목을 입력하세요'];
			}

			if (isset($postData['dealings_content']) && empty($postData['dealings_content'])) {
				return ['isValid'=>false, 'errorMessage'=>'내용을 입력하세요'];
			}

			if (isset($postData['item_no']) && empty($postData['item_no'])) {
				return ['isValid'=>false, 'errorMessage'=>'구매하고자 하는 상품권을 선택하세요.'];
			}

			if (isset($postData['item_money']) && empty($postData['item_money'])) {
				return ['isValid'=>false, 'errorMessage'=>'상품권 금액을 선택하세요.'];
			}

			if (isset($postData['item_object_no']) && empty($postData['item_object_no'])) {
				return ['isValid'=>false, 'errorMessage'=>'고유번호를 입력하세요'];
			}

			if (isset($postData['dealings_mileage']) && empty($postData['dealings_mileage'])) {
				return ['isValid'=>false, 'errorMessage'=>'거래금액을 입력하세요'];
			}

			return ['isValid'=>true, 'errorMessage'=>''];
		}

		/**
		 * 거래글 생성 프로세스 
		 *
		 * @param array @param 거래 생성시 필요한 데이터를 배열로 전달
		 * 
		 * @return array
		 */
		public function dealingsInsertProcess($param)
		{
			// 거래생성
			$cDealingsQ = 'INSERT INTO `th_dealings` SET
							`expiration_date` = ?,
							`register_date` = ?,
							`dealings_type` = ?,
							`dealings_subject` = ?,
							`dealings_content` = ?,
							`item_no` = ?,
							`item_money` = ?,
							`item_object_no` = ?,
							`dealings_mileage` = ?,
							`dealings_commission` = ?,
							`dealings_status` = ?,
							`memo` = ?,
							`writer_idx` = ?';
			
			$this->db->execute($cDealingsQ, $param);

			$dealingsInsertId = $this->db->insert_id();
			if ($dealingsInsertId < 1) {
				return [
					'result'=> false,
					'resultMessage'=> '거래글 등록하면서 오류가 발생했습니다.'
				];
			}

			// 거래 처리과정 생성
			$cProcessP = [
				'dealings_idx'=> $dealingsInsertId,
				'dealings_status_idx'=> 1
			];

			$cDealingsProcessQ = 'INSERT INTO `th_dealings_process` SET
									`dealings_idx` = ?,
									`dealings_status_idx` = ?,
									`dealings_datetime` = now()';
				
			$this->db->execute($cDealingsProcessQ, $cProcessP);

			$dealingsProcessId = $this->db->insert_id();
			if ($dealingsProcessId < 1) {
				return [
					'result'=> false,
					'resultMessage'=> '거래 처리과정 생성하면서 오류가 발생했습니다.'
				];
			}

			return [
				'result'=> true,
				'resultMessage'=> '',
				'insertId'=> $dealingsInsertId
			];
		}
		
		/**
		 * 거래 상태 프로세스 
		 *
		 * @param array $param 거래 상태 프로세스 수정하기 위한 정보을 담은 배열
		 *
		 * @return array
		 */
		public function dealignsStatusProcess($param)
		{
			$dealingsIdx = $param['dealings_idx'];
			$dealingsStatus = $param['dealings_status'];

			if (isset($param['member_idx'])) {
                $memberIdx = $param['member_idx'];
            }

			$cDealingsUserInsertId = '';

			// 거래 유저 테이블에 데이터 있는지 확인
			$rUserCountQ = 'SELECT COUNT(`idx`) cnt 
							FROM `th_dealings_user` 
							WHERE `dealings_idx` = ?';
			
			$rUserCountResult = $this->db->execute($rUserCountQ, $dealingsIdx);
			if ($rUserCountResult === false) {
				return [
					'result'=> false,
					'resultMessage'=> '거래 유저 테이블 카운트하면서 오류가 발생하였습니다.'
				];
			}

			$dealingsUserCnt = $rUserCountResult->fields['cnt'];

			$rDealingsStatusQ = 'SELECT dealings_status 
								 FROM `th_dealings_user` 
								 WHERE `dealings_idx` = ?';
			
			$rDealingsStatusResult = $this->db->execute($rDealingsStatusQ, $dealingsIdx);
			if ($rDealingsStatusResult === false) {
				return [
					'result'=> false,
					'resultMessage'=> '거래 상태가 변경되었는지 확인하는중에 오류가 발생했습니다.'
				];
			}

			$changeStatus = $rDealingsStatusResult->fields['dealings_status'];

			if ($dealingsUserCnt == 0) { 
				// 거래유저가 없는 경우 증가하여 처리 (거래대기인 경우)
				$dealingsStatus += 1;
			}

			if ($changeStatus == $dealingsStatus) {
				$dealingsStatus += 1;
			}

			$uDealingsP = [
				'dealings_status'=> $dealingsStatus,
				'dealings_idx'=> $dealingsIdx
			];

			$uDealingsQ = 'UPDATE `th_dealings` SET 
							`dealings_status` = ? 
							WHERE `idx` = ?';

			$this->db->execute($uDealingsQ, $uDealingsP);

			$dealingsAffectRow = $this->db->affected_rows();
			if ($dealingsAffectRow < 0) {
				return [
					'result'=> false,
					'resultMessage'=> '거래테이블 상태 수정하면서 오류가 발생하였습니다.'
				];
			}

			// 거래 기본정보 DB에서 받아오기
			$rDealingsQ = 'SELECT `dealings_type`,
								  `dealings_status`,
								  `writer_idx`,
								  `idx`
							FROM `th_dealings`
							WHERE `idx` = ?
							FOR UPDATE';
			
			$rDealingsResult = $this->db->execute($rDealingsQ, $dealingsIdx);
			if ($rDealingsResult === false) {
				return [
					'result'=> false,
					'resultMessage'=> '거래 정보 조회 하면서 오류가 발생하였습니다.'
				];
			}

			$dealingsNewIdx = $rDealingsResult->fields['idx'];
			if ($dealingsUserCnt == 0) {
				$cDealingsUserP = [
					'dealings_idx'=> $dealingsNewIdx,
					'dealings_writer_idx'=> $rDealingsResult->fields['writer_idx'],
					'dealings_member_idx'=> $memberIdx,
					'dealings_status'=> $dealingsStatus,
					'dealings_type'=> $rDealingsResult->fields['dealings_type']
				];

				$cDealingsUserQ = 'INSERT INTO `th_dealings_user` SET
									`dealings_idx` = ?,
									`dealings_writer_idx` = ?,
									`dealings_member_idx` = ?,
									`dealings_status` = ?,
									`dealings_type` = ?,
									`dealings_date` = curdate()';
			
				$this->db->execute($cDealingsUserQ, $cDealingsUserP);

				$cDealingsUserInsertId = $this->db->insert_id();
				if ($cDealingsUserInsertId < 1) {
					return [
						'result'=> false,
						'resultMessage'=> '거래 유저를 추가하면서 오류가 발생하였습니다.'
					];
				}
			} else {
				// 해당 거래글이 있는 경우 수정
				$uDealingsUserP = [
					'dealings_status'=> $dealingsStatus,
					'dealings_idx'=> $rDealingsResult->fields['idx']
				];

				$uDealingsUserQ = 'UPDATE `th_dealings_user` SET 
									`dealings_status` = ?,
									`dealings_date` = curdate()
									WHERE `dealings_idx` = ?';

				$this->db->execute($uDealingsUserQ, $uDealingsUserP);

				$uDealingsUsersAffectedRow = $this->db->affected_rows();
				if ($uDealingsUsersAffectedRow < 0) {
					return [
						'result'=> false,
						'resultMessage'=> '거래 유저를 수정하면서 오류가 발생했습니다.'
					];
				}
			}

			$cDealingsPsP = [
				'dealings_status_idx' => $dealingsStatus,
				'dealings_idx' => $dealingsIdx
			];

			$cDealingsPsQ = 'INSERT INTO `th_dealings_process` SET
								`dealings_status_idx` = ?,
								`dealings_idx` = ?,
								`dealings_datetime` = now()';

			$this->db->execute($cDealingsPsQ, $cDealingsPsP);

			$dealingsInsertId = $this->db->insert_id();
			if ($dealingsInsertId < 1) {
				return [
					'result'=> false,
					'resultMessage'=> '거래 프로세스 추가 하면서 오류가 발생했습니다.'
				];
			}

			return [
				'result'=> true,
				'resultMessage'=> '',
				'cDealingsUserInserId'=> $cDealingsUserInsertId
			];
		}
	}
