<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 세션 체크 후 페이지 이동 
	 */

	if(!isset($_SESSION['idx'])){
		if(isset($_SESSION['tmp_idx'])){
			// 회원가입했을 때 임시세션 제거	
			unset($_SESSION['tmp_idx']);
		}else{
			$returnUrl = SITE_DOMAIN;
			alertMsg($returnUrl,1,'로그인을 해야 이용 가능합니다!');
		}
	}