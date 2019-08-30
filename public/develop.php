<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 개발 테스트 할 때 사용하는 파일. 
	 */

	// 환경설정
	include $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	// 공용함수
	include $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	// PDO 객체 생성
	include $_SERVER['DOCUMENT_ROOT'] . '/../includes/databaseConnection.php';

	
	//암호화
	//43255821107256(14)
	//23911431181092(14)
	//13470179379291(14)
	//echo setEncrypt('134701793792917');

	echo strlen('f1FdY3G8ZwDH2bWgX19hAw==');

	//복호화
	//echo setDecrypt('rTIaEVLQRc8JRhG/9Qi+4Q==');

	// 날짜계산
	$date = date('2019-06-20');
	//echo date('Y-m-d',strtotime($date.'+4 month'));
