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

	abstract class QuerySkeleton implements Query
	{
		protected $where		= array();	// where clauses
		protected $whereLogic	= array();	// logic between where's

		public function getId()
		{
			static $dialect = null;
			
			if ($dialect === null)
				$dialect = new ImaginaryDialect();

			return sha1($this->toString($dialect));
		}
		
		public function setId($id)
		{
			throw new UnsupportedMethodException();
		}

		public function where(LogicalObject $exp, $logic = null)
		{
			if (sizeof($this->where) > 0 && !$logic)
				throw new WrongArgumentException(
					'you have to specify expression logic'
				);
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

		public function toString(Dialect $dialect)
		{
			if ($this->where) {
				$clause = ' WHERE';
				$outputLogic = false;
				
				for ($i = 0; $i < sizeof($this->where); $i++)
					if ($exp = $this->where[$i]->toString($dialect)) {
						$clause .= "{$this->whereLogic[$i]} {$exp} ";
						$outputLogic = true;
					}
					elseif (!$outputLogic && isset($this->whereLogic[$i + 1]))
						$this->whereLogic[$i + 1] = null;

				return $clause;
			}
			
			return null;
		}
	}
?>