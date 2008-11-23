<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup OSQL
	**/
	abstract class QuerySkeleton extends QueryIdentification
	{
		protected $where		= array();	// where clauses
		protected $whereLogic	= array();	// logic between where's

		public function where(LogicalObject $exp, $logic = null)
		{
			if ($this->where && !$logic)
				throw new WrongArgumentException(
					'you have to specify expression logic'
				);
			else {
				if (!$this->where && $logic)
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

		public function toString(Dialect $dialect)
		{
			if ($this->where) {
				$clause = ' WHERE';
				$outputLogic = false;
				
				for ($i = 0, $size = count($this->where); $i < $size; ++$i) {
					
					if ($exp = $this->where[$i]->toString($dialect)) {
						
						$clause .= "{$this->whereLogic[$i]} {$exp} ";
						$outputLogic = true;
						
					} elseif (!$outputLogic && isset($this->whereLogic[$i + 1]))
						$this->whereLogic[$i + 1] = null;
					
				}

				return $clause;
			}
			
			return null;
		}
	}
?>