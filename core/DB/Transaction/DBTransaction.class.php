<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Database transaction implementation.
	 * 
	 * @ingroup Transaction
	**/
	final class DBTransaction extends BaseTransaction
	{
		private $started	= false;
		
		public function __destruct()
		{
			if ($this->isStarted())
				$this->db->queryRaw("rollback;\n");
		}
		
		/**
		 * @return DBTransaction
		**/
		public function setDB(DB $db)
		{
			if ($this->isStarted())
				throw new WrongStateException(
					'transaction already started, can not switch to another db'
				);

			return parent::setDB($db);
		}
		
		public function isStarted()
		{
			return $this->started;
		}
		
		/**
		 * @return DBTransaction
		**/
		public function add(Query $query)
		{
			if (!$this->isStarted()) {
				$this->db->queryRaw($this->getBeginString());
				$this->started = true;
			}
			
			$this->db->queryNull($query);
			
			return $this;
		}
		
		/**
		 * @return DBTransaction
		**/
		public function flush()
		{
			$this->started = false;
			
			try {
				$this->db->queryRaw("commit;\n");
			} catch (DatabaseException $e) {
				$this->db->queryRaw("rollback;\n");
				throw $e;
			}
			
			return $this;
		}
	}
?>