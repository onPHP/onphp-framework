<?php
/***************************************************************************
 *   Copyright (C) 2012 by Alexey S. Denisov                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Exception which must be thrown if you need to make rollback transaction
	 *   in InnerTransaction::wrap
	 * 
	 * @ingroup Transaction
	**/
	class InnerTransactionWrapperException extends BaseException
	{
		private $returnValue = null;
		
		/**
		 * @param type $returnValue
		 * @return InnerTransactionWrapperException
		 */
		public static function createValue($returnValue)
		{
			$exception = new self();
			return $exception->setReturnValue($returnValue);
		}

		/**
		 * @param mixed $returnValue
		 * @return InnerTransactionWrapperException 
		**/
		public function setReturnValue($returnValue)
		{
			$this->returnValue = $returnValue;
			return $this;
		}
		
		/**
		 * @return mixed
		**/
		public function getReturnValue()
		{
			return $this->returnValue;
		}
	}
?>