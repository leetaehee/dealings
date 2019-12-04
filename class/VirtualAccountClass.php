<?php
    /** 
	 * 가상 계좌 클래스 
     */
	class VirtualAccountClass
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
         * 가상계좌 생성 시 유효성 검증
         *
         * @param array $postData
         *
         * @return array
         */
		public function checkFormValidate($postData)
		{
			if (isset($postData['accountBank']) && empty($postData['accountBank'])) {
				return ['isValid'=>false, 'errorMessage'=>'입금은행을 입력하세요'];
			}

			return ['isValid'=>true, 'errorMessage'=>''];
		}
	}