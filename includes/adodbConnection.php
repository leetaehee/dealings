<?php
	/**
	 * ado db 커넥션 생성 
	 */

	//test

	$driver = 'mysqli';

	$db = newADOConnection('mysqli');
	$connection = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

	// 크론탭에서 오류발생함. config 분리하면서 따로 뺄것.
	/*
        $requestURI = $_SERVER['REQUEST_URI'];
        $debugAllowURL = [
            '/process/virtual/virtual_account_ajax.php',
            '/process/member/member_ajax_process.php',
            '/process/admin/admin_ajax_process.php'
        ];

        if (!in_Array($requestURI, $debugAllowURL)) {
            // ajax에서는 쿼리 읽어들이지말것..
            //$db->debug  = true;
        }
	*/