<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: ado db 커넥션 생성 
	 */

	$driver = 'mysqli';

	$db = newADOConnection('mysqli');
	//$db->debug = true; // display error message
	$db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);