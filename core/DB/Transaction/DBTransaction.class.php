<?php
/***************************************************************************
 *   Copyright (C) 2005-2006 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
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
		
		public function add(Query $query)
		{
			if (!$this->isStarted()) {
				$this->db->queryRaw($this->getBeginString());
				$this->started = true;
			}
			
			$this->db->queryNull($query);
			
			return $this;
		}
		
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