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
	 * Utility to wrap function into transaction
	 * 
	 * @ingroup Transaction
	**/
	final class InnerTransactionWrapper
	{
		private $db = null;
		private $dao = null;
		private $function = null;
		private $level = null;
		private $mode = null;
		
		/**
		 * @return InnerTransactionWrapper
		 */
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @param DB $db
		 * @return InnerTransactionWrapper
		 */
		public function setDB(DB $db)
		{
			$this->db = $db;
			return $this;
		}
		
		/**
		 * @param StorableDAO $dao
		 * @return InnerTransactionWrapper 
		 */
		public function setDao(StorableDAO $dao)
		{
			$this->dao = $dao;
			return $this;
		}
		
		/**
		 * @param mixed $function
		 * @return InnerTransactionWrapper 
		 */
		public function setFunction($function)
		{
			$this->function = $function;
			return $this;
		}

		/**
		 * @param IsolationLevel $level
		 * @return InnerTransactionWrapper 
		 */
		public function setLevel(IsolationLevel $level)
		{
			$this->level = $level;
			return $this;
		}

		/**
		 * @param AccessMode $mode
		 * @return InnerTransactionWrapper 
		 */
		public function setMode(AccessMode $mode)
		{
			$this->mode = $mode;
			return $this;
		}
		
		public function run()
		{
			Assert::isTrue(!is_null($this->dao) || !is_null($this->db), 'set first dao or db');
			Assert::isNotNull($this->function, 'set first function');
			
			$transaction = InnerTransaction::begin(
				$this->dao ?: $this->db,
				$this->level,
				$this->mode
			);
			
			try {
				$result = call_user_func_array($this->function, func_get_args());
				$transaction->commit();
				return $result;
			} catch (InnerTransactionWrapperException $e) {
				$transaction->rollback();
				return $e->getReturnValue();
			} catch (Exception $e) {
				$transaction->rollback();
				throw $e;
			}
		}
	}
?>