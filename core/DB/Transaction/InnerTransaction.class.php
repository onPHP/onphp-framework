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
	 * Utility to create transaction and not think about current nested level
	 * 
	 * @ingroup Transaction
	**/
	final class InnerTransaction
	{
		/**
		 * @var DB
		**/
		private $db = null;
		private $savepointName = null;
		private $finished = false;
		
		/**
		 * @param DB|GenericDAO $database
		 * @param IsolationLevel $level
		 * @param AccessMode $mode
		 * @return InnerTransaction
		**/
		public static function begin(
			$database,
			/* IsolationLevel */ $level = null,
			/* AccessMode */ $mode = null
		)
		{
			return new self($database, $level, $mode);
		}
		
		/**
		 * @param DB|GenericDAO $database
		 * @param IsolationLevel $level
		 * @param AccessMode $mode
		**/
		public function __construct(
			$database,
			/* IsolationLevel */ $level = null,
			/* AccessMode */ $mode = null
		)
		{
			if ($database instanceof DB) {
				$this->db = $database;
			} elseif ($database instanceof GenericDAO) {
				$this->db = DBPool::getByDao($database);
			} else {
				throw new WrongStateException(
					'$database must be instance of DB or GenericDAO'
				);
			}
			
			$this->beginTransaction($level, $mode);
		}
		
		public function commit()
		{
			$this->assertFinished();
			$this->finished = true;
			if (!$this->savepointName) {
				$this->db->commit();
			} else {
				$this->db->queryRaw("release savepoint {$this->savepointName};");
			}
		}
		
		public function rollback()
		{
			$this->assertFinished();
			$this->finished = true;
			if (!$this->savepointName) {
				$this->db->rollback();
			} else {
				$this->db->queryRaw("rollback to savepoint {$this->savepointName};");
			}
		}
		
		private function beginTransaction(
			/* IsolationLevel */ $level = null,
			/* AccessMode */ $mode = null
		)
		{
			$this->assertFinished();
			if (!$this->db->inTransaction()) {
				$this->db->begin($level, $mode);
			} else {
				$this->savepointName = $this->createSavepointName();
				$this->db->queryRaw("savepoint {$this->savepointName};");
			}
		}
		
		private function assertFinished()
		{
			if ($this->finished)
				throw new WrongStateException('This Transaction already finished');
		}
		
		private static function createSavepointName()
		{
			static $i = 1;
			return 'innerSavepoint'.($i++);
		}
	}
?>