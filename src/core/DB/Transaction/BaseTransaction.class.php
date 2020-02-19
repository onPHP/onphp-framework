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

	/**
	 * Transaction's basis.
	 * 
	 * @ingroup Transaction
	**/
	abstract class BaseTransaction
	{
		protected $db		= null;
		
		protected $isoLevel	= null;
		protected $mode		= null;
		
		abstract public function flush();
		
		public function __construct(DB $db)
		{
			$this->db = $db;
		}
		
		/**
		 * @return BaseTransaction
		**/
		public function setDB(DB $db)
		{
			$this->db = $db;
			
			return $this;
		}
		
		/**
		 * @return DB
		**/
		public function getDB()
		{
			return $this->db;
		}
		
		/**
		 * @return BaseTransaction
		**/
		public function setIsolationLevel(IsolationLevel $level)
		{
			$this->isoLevel = $level;
			
			return $this;
		}
		
		/**
		 * @return BaseTransaction
		**/
		public function setAccessMode(AccessMode $mode)
		{
			$this->mode = $mode;
			
			return $this;
		}
		
		protected function getBeginString()
		{
			$begin = 'start transaction';
			
			if ($this->isoLevel)
				$begin .= ' '.$this->isoLevel->toString();
			
			if ($this->mode)
				$begin .= ' '.$this->mode->toString();
			
			return $begin.";\n";
		}
	}
?>