<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 환경설정(DB,Session,Cookies, 에러메세지 처리) 
	 */

	// 세션 활성화 
	session_start();

	/**
	 * 상수
	 */

	// DB 커넥션정보
	define('DB_HOST', 'localhost'); 
	define('DB_USER', 'imi');
	define('DB_NAME', 'imi');
	define('DB_PASSWORD', 'imith190819@');

	// 메세지 상수(DB)
	define('DB_CONNECTION_ERROR_MESSAGE', '데이터베이스 서버에 접속 할 수 없습니다: ');

	// 사이트 도메인 
	define('SITE_DOMAIN', 'http://imi.th-study.co.kr');
	define('SITE_ADMIN_DOMAIN', 'http://imi.th-study.co.kr/admin');

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

	// process (관리자)
	define('ADMIN_PROCESS_ACTION', SITE_DOMAIN . '/process/admin'); // 로그인

	// 암호화 및 복호화 상수 
	define('ENCRYPT_TYPE', 'aes-256-cbc');
	define('ENCRYPT_KEY', 'imi_key');

	/**
	 * 변수
	 */ 
	$ajaxUrl = '';
	$JsTemplateUrl = '';