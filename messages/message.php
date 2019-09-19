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

	define('TITLE_SITE_NAME', 'IMI 회원정보 시스템'); // 사이트명
	define('TITLE_HOME_MENU', '홈'); // Home 메뉴
	define('TITLE_LOGIN_MENU', '로그인'); // 로그인 메뉴
	define('TITLE_LOGOUT_MENU', '로그아웃'); // 로그아웃 메뉴
	define('TITLE_JOIN_MENU', '회원가입'); // 회원가입 메뉴
    define('TITLE_MODIFY_MENU', '회원정보수정'); // 회원정보수정
	define('TITLE_JOIN_COMPLETE_MENU', '회원가입 완료'); // 회원가입 완료 후 보여지는 메뉴
	define('TITLE_JOIN_APPROVAL', '회원가입 승인대기'); // 회원가입 승인대기화면 
	define('TITLE_MYPAGE_MENU', '회원정보'); // 마이페이지 메뉴
	define('TITLE_MILEAGES_MENU', '마일리지 현황'); // 마일리지 현황 메뉴
	define('TITLE_SITE_MAIN', '메인화면'); // 메인화면(로그인 하고나서)
    define('TITLE_MYPAGE_DEL_MENU', '회원탈퇴'); // 회원탈퇴
	define('TITLE_LOGIN_LIST', '로그인내역'); // 로그인내역
	define('TITLE_MY_ACCOUNT_SETTING', '출금계좌 내역'); // 출금계좌
	define('TITLE_VIRTUAL_MILEGE_CHARGE', '가상계좌 충전'); // 가상계좌 충전
	define('TITLE_CARD_MILEGE_CHARGE', '신용카드 충전'); // 신용카드 충전
	define('TITLE_PHONE_MILEGE_CHARGE', '휴대전화 충전'); // 휴대전화 충전
	define('TITLE_VOUCHER_MILEGE_CHARGE', '문화상품권 충전'); // 문화상품권 충전
	define('TITLE_VIRTUAL_MILEGE_WITHDRAWAL', '가상계좌 출금'); // 마일리지 출금
	define('TITLE_MY_CHARGE_LIST', '마일리지 충전내역'); // 마일리지 충전내역
	define('TITLE_MY_WITHDRAWAL_LIST', '마일리지 출금내역'); // 마일리지 출금내역
	define('TITLE_VOUCHER_DEALINGS_HOME', '상품권 거래'); // 상품권 거래 홈
	define('TITLE_VOUCHER_PURCHASE_ENROLL', '상품권 구매등록'); // 상품권 구매 등록
	define('TITLE_VOUCHER_SELL_ENROLL', '상품권 판매등록'); // 상품권 판매 등록
	define('TITLE_VOUCHER_PURCHASE_LIST', '상품권 구매목록'); // 상품권 구매 목록
	define('TITLE_VOUCHER_SELL_LIST', '상품권 판매목록'); // 상품권 판매 목록
	define('TITLE_VOUCHER_PURCHASE_DETAIL_VIEW', '상품권 구매 거래 상세내역'); // 상품권 구매 거래 상세화면
	define('TITLE_VOUCHER_SELL_DETAIL_VIEW', '상품권 판매 거래 상세내역'); // 상품권 판매 거래 상세화면
	define('TITLE_VOUCHER_PURCHASE_STATUS', '상품권 구매 결제현황'); // 상품권 구매 결제 현황 목록
	define('TITLE_VOUCHER_SELL_STATUS', '상품권 판매 결제현황'); // 상품권 판매 결제 현황 목록
	define('TITLE_VOUCHER_PURCHASE_ENROLL_STATUS', '상품권 구매 등록현황'); // MY 구매 등록 현황 목록
	define('TITLE_VOUCHER_SELL_ENROLL_STATUS', '상품권 판매 등록현황'); // MY 판매 등록 현황 목록

	define('TITLE_ADMIN_SITE_NAME', '관리자::IMI 회원정보 시스템'); // 관리자 사이트명
	define('TITLE_ADMIN_MODIFY_MENU', '정보수정'); // 관리자 정보수정
	define('TITLE_ADMIN_PAGE_DEL_MENU', '탈퇴'); // 회원탈퇴
	define('TITLE_ADMIN_LOGIN_LIST', '내역'); // 관리자 로그인내역
	define('TITLE_ADMIN_MENU', '정보'); // 관리자 정보 메뉴
	define('TITLE_ADMIN_MEMBER_STATUS', '회원관리'); // 회원현황
	define('TITLE_ADMIN_CHARGE_STATUS', '마일리지 결제내역'); // 결제내역
	define('TITLE_ADMIN_DEALINGS_STATUS', '상품권 거래관리'); // 거래관리

	/*
	 * @author: LeeTaeHee
	 * @brief: 회원가입시 사용자에게 보여지는 안내 메세지 
	 */

	 define('JOIN_FORM_EMAIL_CAUTION_WRITE', '(imi@imi.com과 같이 입력해주세요.)');
	 define('JOIN_FORM_PHONE_CAUTION_WRITE', '(휴대번호에는 하이픈(\'-\')을 넣을 수없습니다.)');	
	 define('JOIN_FORM_BIRTH_CAUTION_WRITE', '(1989-11-17와 같이 입력해주세요.)');
	 define('JOIN_FORM_ACCOUNT_CAUTION_WRITE', '(계좌번호에는 하이픈을 제거해주세요.)');

	 /*
	 * @author: LeeTaeHee
	 * @brief: 메일 관련 
	 */
	 define('MAIL_ADDRESS', 'developerkimtakgu@gmail.com');
	 define('MAIL_SENDER', '이태희 주임');
	 define('MAIL_TITLE', 'IMI 회원정보 시스템 메일 승인 바랍니다!');
