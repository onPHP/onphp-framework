<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
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
	final class DBTable implements DialectString
	{
		private $name		= null;
		
		private $columns	= array();
		private $order		= array();
		
		public static function create($name)
		{
			return new self($name);
		}
		
		public function __construct($name)
		{
			$this->name = $name;
		}
		
		public function getColumns()
		{
			return $this->columns;
		}
		
		public function addColumn(DBColumn $column)
		{
			$name = $column->getName();
			
			if (isset($this->columns[$name]))
				throw new WrongArgumentException(
					"column '{$name}' already exist"
				);
			
			$this->order[] = $this->columns[$name] = $column;
			
			$column->setTable($this);
			
			return $this;
		}
		
		public function getColumnByName($name)
		{
			if (!isset($this->columns[$name]))
				throw new WrongArgumentException(
					"column '{$name}' does not exist"
				);
			
			return $this->columns[$name];
		}
		
		public function dropColumnByName($name)
		{
			if (!isset($this->columns[$name]))
				throw new WrongArgumentException(
					"column '{$name}' does not exist"
				);
			
			unset($this->columns[$name]);
			unset($this->order[array_search($name, $this->order)]);
			
			return $this;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		public function getOrder()
		{
			return $this->order;
		}
		
		public function toString(Dialect $dialect)
		{
			return OSQL::createTable($this)->toString($dialect);
		}
	}
?>