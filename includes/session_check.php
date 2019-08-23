<?php
	if(!isset($_SESSION['idx'])){
		if(isset($_SESSION['tmp_idx'])){
			// 회원가입했을 때 임시세션 제거	
			unset($_SESSION['tmp_idx']);
		}else{
			header('location: '.SITE_DOMAIN);
		}
	}