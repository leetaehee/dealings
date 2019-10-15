<?php
	/**
	 * 트랜잭션이 롤백 되었을 때 예외 실행
	 */
	Class RollbackException extends Exception
	{
		public function errorMessage()
		{
			return $this->getMessage();
		}
	}