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
	 * Transaction's basis.
	 * 
	 * @ingroup DB
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
		
		public function setDB(DB $db)
		{
			$this->db = $db;
			
			return $this;
		}
		
		public function getDB()
		{
			return $this->db;
		}
		
		public function setIsolationLevel(IsolationLevel $level)
		{
			$this->isoLevel = $level;
			
			return $this;
		}
		
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