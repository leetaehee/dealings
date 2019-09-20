<?php
	/**
	 * @file CompleteException.php
	 * @brief 트랜잭션이 완료가 되었을 때 실행
	 * @author 이태희
	 */
	Class CompleteException extends Exception
	{
		public function message()
		{
			return $this->getMessage();
		}
	}