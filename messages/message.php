<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 메세지 상수 (사용자에게 보여지는 메세지) 
	 */

	
	/*
	 * @author: LeeTaeHee
	 * @brief: 
			1. <title>태그에 메세지 설정 
			2. 표기- 메뉴명 | 사이트명 
			3. 예시- <title>로그인 | IMI 회원정보 시스템</title>  
	 */

	define('TITLE_SITE_NAME','IMI 회원정보 시스템'); // 사이트명
	define('TITLE_HOME_MENU','홈'); // Home 메뉴
	define('TITLE_LOGIN_MENU','로그인'); // 로그인 메뉴
	define('TITLE_LOGOUT_MENU','로그아웃'); // 로그아웃 메뉴
	define('TITLE_JOIN_MENU','회원가입'); // 회원가입 메뉴
	define('TITLE_JOIN_COMPLETE_MENU','회원가입 완료'); // 회원가입 완료 후 보여지는 메뉴
	define('TITLE_JOIN_APPROVAL','회원가입 승인대기'); // 회원가입 승인대기화면 
	define('TITLE_MYPAGE_MENU','회원정보'); // 마이페이지 메뉴
	define('TITLE_ADMIN_MENU','관리자'); // 관리자 메뉴

	/*
	 * @author: LeeTaeHee
	 * @brief: 회원가입시 사용자에게 보여지는 안내 메세지 
	 */
	 define('JOIN_FORM_EMAIL_CAUTION_WRITE','(imi@imi.com과 같이 입력해주세요.)');
	 define('JOIN_FORM_PHONE_CAUTION_WRITE','(휴대번호에는 하이픈(\'-\')을 넣을 수없습니다.)');	
	 define('JOIN_FORM_BIRTH_CAUTION_WRITE','(1989-11-17와 같이 입력해주세요.)');

	 /*
	 * @author: LeeTaeHee
	 * @brief: 메일 관련 
	 */
	 define('MAIL_ADDRESS','developerkimtakgu@gmail.com');
	 define('MAIL_SENDER','이태희 주임');
	 define('MAIL_TITLE','IMI 회원정보 시스템 메일 승인 바랍니다!');
