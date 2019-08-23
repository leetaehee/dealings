<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: PDO 객체 생성 
	 */
	$pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME,DB_USER,DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);