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
	namespace Onphp;

	final class InnerTransactionWrapper
	{
		/**
		 * @var \Onphp\DB
		 */
		private $db = null;
		/**
		 * @var \Onphp\StorableDAO
		 */
		private $dao = null;
		private $function = null;
		private $exceptionFunction = null;
		/**
		 * @var \Onphp\IsolationLevel
		 */
		private $level = null;
		/**
		 * @var \Onphp\AccessMode
		 */
		private $mode = null;
		
		/**
		 * @return \Onphp\InnerTransactionWrapper
		 */
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @param \Onphp\DB $\Onphp\DB
		 * @return \Onphp\InnerTransactionWrapper
		 */
		public function setDB(DB $db)
		{
			$this->db = $db;
			return $this;
		}
		
		/**
		 * @param \Onphp\StorableDAO $dao
		 * @return \Onphp\InnerTransactionWrapper
		 */
		public function setDao(StorableDAO $dao)
		{
			$this->dao = $dao;
			return $this;
		}
		
		/**
		 * @param callable $function
		 * @return \Onphp\InnerTransactionWrapper
		 */
		public function setFunction($function)
		{
			Assert::isTrue(is_callable($function, false), '$function must be callable');
			$this->function = $function;
			return $this;
		}
		
		/**
		 * @param callable $function
		 * @return \Onphp\InnerTransactionWrapper
		 */
		public function setExceptionFunction($function)
		{
			Assert::isTrue(is_callable($function, false), '$function must be callable');
			$this->exceptionFunction = $function;
			return $this;
		}

		/**
		 * @param \Onphp\IsolationLevel $level
		 * @return \Onphp\InnerTransactionWrapper
		 */
		public function setLevel(IsolationLevel $level)
		{
			$this->level = $level;
			return $this;
		}

		/**
		 * @param \Onphp\AccessMode $mode
		 * @return \Onphp\InnerTransactionWrapper
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
			} catch (\Exception $e) {
				$transaction->rollback();
				if ($this->exceptionFunction) {
					$args = func_get_args();
					array_unshift($args, $e);
					return call_user_func_array($this->exceptionFunction, $args);
				}
				throw $e;
			}
		}
	}