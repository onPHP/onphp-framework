<?php
/***************************************************************************
 *   Copyright (C) 2005-2009 by Anton E. Lebedevich                        *
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
	 * @ingroup Module
	**/
	class OrderBy extends FieldTable implements MappableObject
	{
		private $direction	= null;
		private $nulls		= null;
		
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
			$this->nulls = new Ternary(null);
		}
		
		public function __clone()
		{
			$this->direction = clone $this->direction;
			$this->nulls = clone $this->nulls;
		}
		
		/**
		 * @return OrderBy
		**/
		public function setDirection($direction)
		{
			$this->direction->setValue($direction);
			return $this;
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
		public function nullsFirst()
		{
			$this->nulls->setTrue();
			return $this;
		}
		
		/**
		 * @return OrderBy
		**/
		public function nullsLast()
		{
			$this->nulls->setFalse();
			return $this;
		}
		
		public function isNullsFirst()
		{
			return $this->nulls->decide(true, false, true);
		}
		
		/**
		 * @return OrderBy
		**/
		public function setNullsFirst($nullsFirst)
		{
			$this->nulls->setValue($nullsFirst);
			return $this;
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
		public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
		{
			$order = self::create($dao->guessAtom($this->field, $query));
			
			if (!$this->nulls->isNull())
				$order->setNullsFirst($this->nulls->getValue());
			
			if (!$this->direction->isNull())
				$order->setDirection($this->direction->getValue());
			
			return $order;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			if (
				$this->field instanceof SelectQuery
				|| $this->field instanceof LogicalObject
			)
				$result = '('.$dialect->fieldToString($this->field).')';
			else
				$result = parent::toDialectString($dialect);
			
			$result .=
				$this->direction->decide(' ASC', ' DESC')
				.$this->nulls->decide(' NULLS FIRST', ' NULLS LAST');
			
			return $result;
		}
		
		public function getFieldName()
		{
			if ($this->field instanceof DBField)
				return $this->field->getField();
			else
				return $this->field;
		}
	}
?>