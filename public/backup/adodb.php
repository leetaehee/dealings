<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 회원가입 화면 
	 */
	
	// 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	// 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	// 공용함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	/*
	 * 쿼리실행 : extecute(..);
	 * 실행 시 반환되는 row 갯수 : affected_rows()
	 */
	/*  쿼리실행 예시 
		$idx = 1;
		$result = $db->execute("SELECT * FROM `imi_members`");
		var_dump2($result);
	*/

