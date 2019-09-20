<?php
	/**
	 * @file RollbackException.php
	 * @brief 트랜잭션이 롤백 되었을 때 실행
	 * @author 이태희
	 */
	Class RollbackException extends Exception
	{
		public function errorMessage()
		{
			return $this->getMessage();
		}
	}