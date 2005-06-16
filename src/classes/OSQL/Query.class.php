<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	abstract class Query
	{
		protected $where			= array();	// where clauses
		protected $whereLogic		= array();	// logic between where's

		public function where(LogicalObject $exp, $logic = null)
		{
			if (sizeof($this->where) > 0 && !$logic)
				throw new WrongArgumentException('you have to specify expression logic');
			else {
				if (sizeof($this->where) == 0 && $logic)
					$logic = null;

				$this->whereLogic[] = $logic;
				$this->where[] = $exp;
			}
			
			return $this;
		}
		
		public function andWhere(LogicalObject $exp)
		{
			return $this->where($exp, 'AND');
		}
		
		public function orWhere(LogicalObject $exp)
		{
			return $this->where($exp, 'OR');
		}

		public function getHash()
		{
			return sha1(serialize($this));
		}

		public function toString(DB $db)
		{
			if ($this->where) {
				$clause = ' WHERE';

				for ($i = 0; $i < sizeof($this->where); $i++)
					if ($exp = $this->where[$i]->toString($db))
						$clause .= "{$this->whereLogic[$i]} {$exp} ";
				
				return $clause;
			}
			
			return null;
		}
	}
?>