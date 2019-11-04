<?php
	/*
	 * 환경설정(DB,Session,Cookies, 에러메세지 처리) 
	 */

	// 세션 활성화 
	session_start();

	/**
	 * 상수
	 */

    // 사이트 도메인
	define('SITE_DOMAIN', 'http://dealings.study');
	define('SITE_ADMIN_DOMAIN', 'http://dealings.study/admin');

	// 루트 경로
    define('SITE_DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

    // front-end(회원)
	define('COMMON_JS_URL', SITE_DOMAIN . '/js/common.js'); // 공통 자바스크립트
	define('JS_URL', SITE_DOMAIN . '/js'); // 자바스크립트 파일 위치
	define('CSS_URL', SITE_DOMAIN . '/css'); // CSS 파일 위치
	define('NOMALIZE_CSS_URL', SITE_DOMAIN . '/css/nomalize.css'); // nomalize.css

	// front-end(관리자)
	define('COMMON_JS_ADMIN_URL', SITE_DOMAIN . '/js/admin/common.js'); // 공통 자바스크립트
	define('JS_ADMIN_URL', SITE_DOMAIN . '/js/admin'); // 자바스크립트 파일 위치
	define('CSS_ADMIN_URL', SITE_DOMAIN . '/css/admin'); // CSS 파일 위치
	define('NOMALIZE_CSS_ADMIN_URL', SITE_DOMAIN . '/css/adminnomalize.css'); // nomalize.css

    // process(회원)
	define('MEMBER_PROCESS_ACTION', SITE_DOMAIN . '/process/member'); // 로그인
	define('MILEAGE_PROCESS_ACTION', SITE_DOMAIN . '/process/mileage'); // 마일리지
	define('LOGIN_PROCESS_ACTION', SITE_DOMAIN . '/process/login'); // 세션 
	define('VIRTUAL_ACCOUNT_PROCESS_ACTION', SITE_DOMAIN . '/process/virtual'); // 마일리지
	define('DEALINGS_PROCESS_ACCTION', SITE_DOMAIN . '/process/dealings'); // 거래
	define('EVENT_PROCEE_ACTION', SITE_DOMAIN . '/process/event'); // 이벤트
	define('COUPON_PROCEE_ACTION', SITE_DOMAIN . '/process/coupon'); // 쿠폰

	// process (관리자)
	define('ADMIN_PROCESS_ACTION', SITE_DOMAIN . '/process/admin'); // 로그인

	// 암호화 및 복호화 상수 
	define('ENCRYPT_TYPE', 'aes-256-cbc');
	define('ENCRYPT_KEY', 'imi_key');

	// ENV 경로 구하기(경로)
    $ENV_PATH =  SITE_DOCUMENT_ROOT . '/../.env.json';
    $ENV = json_decode(file_get_contents($ENV_PATH), true);

    // DB 커넥션정보
    define('DB_HOST', $ENV['db_host']);
    define('DB_USER', $ENV['db_user']);
    define('DB_NAME', $ENV['db_name']);
    define('DB_PASSWORD', $ENV['db_password']);

	/**
	 * 전역변수
	 */ 
	$ajaxUrl = '';
	$JsTemplateUrl = '';
	$templateFileName = '404.html.php';
	$today = date('Y-m-d');

	/**
	 * 배열
	 */

	// 은행 종류
	$CONFIG_BANK_ARRAY = [
		'기업은행','국민은행','신한은행','외환은행','우리은행','부산은행','광주은행','우체국','카카오뱅크'
	];

	// 카드사 종류
	$CONFIG_CARD_ARRAY = [
		'삼성','BC','현대','KB국민','외환','신한','롯데','하나','NH카드'
	];
	
	// 결제 가능한 마일리지
	$CONFIG_MILEAGE_ARRAY = [
		1000,5000,10000,50000,100000
	];

	// 쿠폰 등록시 상품권 리스트 
	$CONFIG_COUPON_VOUCHER_ARRAY = [
		'해피머니상품권','도서문화상품권','문화상품권','스마트문화상품권','모든상품권'
	];

	// 쿠폰 발행 타입 
	$CONFIG_COUPON_ISSUE_TYPE = ['구매','판매'];

	// 상품권 금액
	$CONFIG_VOUCHER_MONEY_ARRAY = [
		1000,5000,10000,50000,100000
	];

	// 판매이벤트 환급률
	$CONFIG_EVENT_SELL_RETRUN_FEE = [
		'1'=> 100,
		'2'=> 50,
		'3'=> 20,
		'4'=> 20,
		'5'=> 20,
		'6'=> 10,
		'7'=> 10,
		'8'=> 10,
		'9'=> 10,
		'10'=> 10
	];

	$CONFIG_EVENT_ARRAY = [
		'구매'=> [
			'seq'=> 1,
			'idx'=> 1,
			'start_date'=> '2019-10-07',
			'end_date'=> '2019-10-31',
			'is_end'=> 'Y',
			'event_type'=> '구매',
			'event_name'=> '구매이벤트'
		],
		'판매'=> [
			'seq'=> 2,
			'idx'=> 1,
			'start_date'=> '2019-10-07',
			'end_date'=> '2019-10-31',
			'is_end'=> 'Y',
			'event_type'=> '판매',
			'event_name'=> '판매이벤트'
		]
	];

	// db테이블도 함께 관리할것
	$CONFIG_DEALINGS_STATUS_CODE = [
		'1'=> '거래대기',
		'2'=> '결제대기',
		'3'=> '결제완료',
		'4'=> '거래완료',
		'5'=> '거래취소',
		'6'=> '거래삭제',
	];

	// db테이블도 함께 관리할것
	$CONFIG_COUPON_STATUS_CODE = [
		'1'=> '사용대기',
		'2'=> '사용완료',
	];

	// 마일리지 유형별 타입 정의
	$CONFIG_MILEAGE_TYPE_COLUMN = [
		'1'=> 'card_sum',
		'2'=> 'phone_sum',
		'3'=> 'culcture_voucher_sum',
		'5'=> 'virtual_account_sum',
		'7'=> 'dealings_sum',
		'8'=> 'event_sum',
	];

	// 페이지 접근금지
	$CONFIG_PROHIBIT_ACCESS = 1;
