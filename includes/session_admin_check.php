<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 관리자 세션 체크 후 페이지 이동 
	 */

	if(!isset($_SESSION['mIdx'])){
		if(isset($_SESSION['tmp_idx'])){	
			unset($_SESSION['tmp_idx']); // 회원가입했을 때 임시세션 제거
		}else{
			$returnUrl = SITE_ADMIN_DOMAIN;
			alertMsg($returnUrl,1,'로그인을 해야 이용 가능합니다!');
		}
	}