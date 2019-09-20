<?php
	/**
	 *  @author: LeeTaeHee
	 *	@brief: 공용함수
	 */

	/**
	 * @author: LeeTaeHee
	 * @param: 패스워드
	 * @brief: 암호화
	 * @return: 암호화 값
	 */
	function setEncrypt($column)
	{
		return openssl_encrypt($column,ENCRYPT_TYPE,ENCRYPT_KEY,false,str_repeat(chr(0),16));
	}

	/**
	 * @author: LeeTaeHee
	 * @param: 패스워드
	 * @brief: 복호화
	 * @return: 복호화 값
	 */
	function setDecrypt($column)
	{
		return openssl_decrypt($column,ENCRYPT_TYPE,ENCRYPT_KEY,false,str_repeat(chr(0),16));
	}

	/**
	 * @author: LeeTaeHee
	 * @param: 연락처
	 * @brief: 연락처에 하이픈 잇는 경우 제거하기
	 * @return: 암호화 값
	 */
	function removeHyphen($phone)
	{
		if (!empty(strstr($phone, '-'))) {
			$phone = str_replace('-', '' , $phone);
		}
		return $phone;			
	}

	/**
	 * @author: LeeTaeHee
	 * @param: 배열
	 * @brief: 타입을 확인 할 수 있는 var_dump() 함수를 줄바꿔서 보여줌 
	 */
	function var_dump2($param)
	{
		echo "<pre>";
		var_dump($param);
		echo "</pre>";
	}

	/**
	 * @author: LeeTaeHee
	 * @param: 배열
	 * @brief: 기존에 print_r(..) 함수를 줄바꿔서 보여줌 
	 */
	function print_r2($param)
	{
		echo "<pre>";
		print_r($param);
		echo "</pre>";
	}

	/**
	 * @author: LeeTaeHee
	 * @param: 문자열 쿼리
	 * @brief: 쿼리를 보여줌.
	 */
	function query_echo($str)
	{
		echo "<pre>";
		echo nl2br($str);
		echo "</pre>";
	}

	/**
	 * @author: LeeTaeHee
	 * @param: URL
	 * @brief: 이동 할 수 있는 함수. 추후 옵션에 따라 분기 처리 할 예정
	 * @return: 없음.
	 */
	function alertMsg($url, $mode = 0, $errorMessage = '')
	{
		if ($mode == 1) {
			echo "<script>
					alert('".$errorMessage."');
					window.location  = '".$url."';
				  </script>
				";
			exit;
		} else if ($mode == 2) {
			echo "<script>
					alert('".$errorMessage."');
				  </script>
				";
			exit;
		} else {
			echo "<script>
					window.location  = '".$url."';
				  </script>
				";
			exit;
		}
	}