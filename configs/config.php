<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 환경설정(DB,Session,Cookies, 에러메세지 처리) 
	 */

	// 세션 활성화 
	session_start();

	// DB 커넥션정보
	define('DB_HOST','localhost'); 
	define('DB_USER','imi');
	define('DB_NAME','imi');
	define('DB_PASSWORD','imith190819@');

	// 메세지 상수(DB)
	define('DB_CONNECTION_ERROR_MESSAGE','데이터베이스 서버에 접속 할 수 없습니다: ');

	// 사이트 도메인 
	define('SITE_DOMAIN','http://imi.th-study.co.kr');

	// form action URL
	define('MEMBER_PROCESS_ACTION',SITE_DOMAIN.'/process/member');