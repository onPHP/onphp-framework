<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup OSQL
	**/
	final class OrderBy extends FieldTable implements MappableObject
	{
		private $direction = null;
		
		/**
		 * @return OrderBy
		**/
		public static function create($field)
		{
			return new self($field);
		}
		
		public function __construct($field)
		{
			parent::__construct($field);
			
			$this->direction = new Ternary(null);
		}
		
		public function __clone()
		{
			$this->direction = clone $this->direction;
		}
		
		/**
		 * @return OrderBy
		**/
		public function desc()
		{
			$this->direction->setFalse();
			return $this;
		}
		
		/**
		 * @return OrderBy
		**/
		public function asc()
		{
			$this->direction->setTrue();
			return $this;
		}
		
		public function isAsc()
		{
			return $this->direction->decide(true, false, true);
		}
		
		/**
		 * @return OrderBy
		**/
		public function invert()
		{
			return
				$this->isAsc()
					? $this->desc()
					: $this->asc();
		}
		
		/**
		 * @return OrderBy
		**/
		public function toMapped(StorableDAO $dao, JoinCapableQuery $query)
		{
			$order = self::create($dao->guessAtom($this->field, $query));
			
			if ($this->direction->isNull())
				return $order;
			
			return $order->{$this->direction->decide('asc', 'desc')}();
		}
		
		public function toDialectString(Dialect $dialect)
		{
			if (
				$this->field instanceof SelectQuery
				|| $this->field instanceof LogicalObject
			)
				return
					'('.$dialect->fieldToString($this->field).')'
					.$this->direction->decide(' ASC', ' DESC');
			else
				return
					parent::toDialectString($dialect)
					.$this->direction->decide(' ASC', ' DESC');
		}
	}
?>