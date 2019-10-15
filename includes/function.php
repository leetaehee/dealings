<?php
	/**
	 * 공용함수
	 */

	/**
	 * 암호화 
	 *
	 * @param string @column 암호화를 해야 하는 변수
	 * 
	 * @return string 
	 */
	function setEncrypt($column)
	{
		return openssl_encrypt($column,ENCRYPT_TYPE,ENCRYPT_KEY,false,str_repeat(chr(0),16));
	}

	/**
	 * 복호화
	 * 
	 * @param string @column 복호화를 해야 하는 변수
	 *
	 * @return string
	 */
	function setDecrypt($column)
	{
		return openssl_decrypt($column,ENCRYPT_TYPE,ENCRYPT_KEY,false,str_repeat(chr(0),16));
	}

	/**
	 * 하이픈을 제거
	 *
	 * @param string @param  하이픈을 제거 해야하는 값 
	 *
	 * @return stirng
 	 */
	function removeHyphen($param)
	{
		if (!empty(strstr($param, '-'))) {
			$param = str_replace('-', '' , $param);
		}
		return $param;			
	}

	/**
	 * var_dump를 가독성 있게 출력 
	 *
	 * @param string $param 출력해야 하는 배열 변수
	 */
	function var_dump2($param)
	{
		echo "<pre>";
		var_dump($param);
		echo "</pre>";
	}

	/**
	 * print_r을 가독성 있게 출력 
	 *
	 * @param string $param 출력해야 하는 배열 변수
	 */
	function print_r2($param)
	{
		echo "<pre>";
		print_r($param);
		echo "</pre>";
	}

	/**
	 * sql을 출력 
	 *
	 * @param string $query 쿼리 변수 
	 */
	function query_echo($query)
	{
		echo "<pre>";
		echo nl2br($query);
		echo "</pre>";
	}

	/**
	 * alert 함수
	 *
	 * @param String $url 이동 url
	 * @param int $mode alert 창을 어떻게 할 것인지 지정
	 * @param string $errorMessage 에러메세지 출력
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