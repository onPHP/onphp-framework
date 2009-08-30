<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup OSQL
	**/
	final class DBColumn implements SQLTableName // also abused in FromTable
	{
		private $type		= null;
		private $name		= null;
		
		private $table		= null;
		private $default	= null;
		
		private $reference	= null;
		private $onUpdate	= null;
		private $onDelete	= null;
		
		private $primary	= null;
		private $unique		= null;
		
		private $sequenced	= null;
		
		public static function create(DataType $type, $name)
		{
			return new DBColumn($type, $name);
		}
		
		public function __construct(DataType $type, $name)
		{
			$this->type = $type;
			$this->name = $name;
		}
		
		public function getType()
		{
			return $this->type;
		}
		
		public function setTable(DBTable $table)
		{
			$this->table = $table;
			
			return $this;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		public function getTable()
		{
			return $this->table;
		}
		
		public function isPrimaryKey()
		{
			return $this->primary;
		}
		
		public function setPrimaryKey($primary = false)
		{
			$this->primary = true === $primary;
			
			return $this;
		}
		
		public function isUnique()
		{
			return $this->unique;
		}
		
		public function setUnique($unique = false)
		{
			$this->unique = true === $unique;
			
			return $this;
		}
		
		public function setDefault($default)
		{
			$this->default = $default;
			
			return $this;
		}
		
		public function getDefault()
		{
			return $this->default;
		}
		
		public function setReference(
			DBColumn $column,
			/* ForeignChangeAction */ $onDelete = null,
			/* ForeignChangeAction */ $onUpdate = null
		)
		{
			Assert::isTrue(
				(
					(null === $onDelete)
					|| $onDelete instanceof ForeignChangeAction
				)
				&& (
					(null === $onUpdate)
					|| $onUpdate instanceof ForeignChangeAction
				)
			);
			
			$this->reference	= $column;
			$this->onDelete		= $onDelete;
			$this->onUpdate		= $onUpdate;
		}
		
		public function dropReference()
		{
			$this->reference	= null;
			$this->onDelete		= null;
			$this->onUpdate		= null;
			
			return $this;
		}
		
		public function setAutoincrement($auto = false)
		{
			$this->sequenced = true === $auto;
			
			return $this;
		}
		
		public function isAutoincrement()
		{
			return $this->sequenced;
		}
		
		public function toString(Dialect $dialect)
		{
			$out =
				"{$dialect->quoteField($this->name)} "
				.$this->type->toString($dialect);
			
			if (null !== $this->default) {
				
				if ($this->type->getId() == DataType::BOOLEAN)
					$default = $this->default ? 'true' : 'false';
				else
					$default = $this->default;
				
				$out .=
					' DEFAULT '
					.(
						$this->default instanceof DialectString
							? $this->default->toString($dialect)
							: $dialect->valueToString($default)
					);
			}
			
			if ($this->reference) {
				
				$table	= $this->reference->getTable()->getName();
				$column	= $this->reference->getName();
				
				$out .=
					" REFERENCES {$dialect->quoteTable($table)}"
					."({$dialect->quoteField($column)})";
				
				if ($this->onDelete)
					$out .= ' ON DELETE '.$this->onDelete->toString();
				
				if ($this->onUpdate)
					$out .= ' ON UPDATE '.$this->onUpdate->toString();
			}
			
			return $out;
		}
	}
?>