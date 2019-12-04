<?php
	/**
     * 로그인 클래스
     */ 
	class LoginClass
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
         * 로그인 유효성 체크
         *
         * @param array $postData
         *
         * @return array
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
	}