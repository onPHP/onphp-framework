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
	 * Single roof for InsertQuery and UpdateQuery.
	 * 
	 * @ingroup OSQL
	**/
	abstract class InsertOrUpdateQuery
		extends QuerySkeleton
		implements SQLTableName
	{
		protected $table	= null;
		protected $fields	= array();
		
		abstract public function setTable($table);
		
		public function getTable()
		{
			return $this->table;
		}

		public function set($field, $value = null)
		{
			$this->fields[$field] = $value;

			return $this;
		}

		public function drop($field)
		{
			if (!array_key_exists($field, $this->fields))
				throw new MissingElementException("unknown field '{$field}'");

			unset($this->fields[$field]);

			return $this;
		}

		public function lazySet($field, /* Identifiable */ $object = null)
		{
			if ($object instanceof Identifiable)
				$this->set($field, $object->getId());
			elseif ($object instanceof Timestamp)
				$this->set($field, $object->toString());
			elseif ($object instanceof Range)
				$this->
					set("{$field}_min", $object->getMin())->
					set("{$field}_max", $object->getMax());
			elseif (is_null($object))
				$this->set($field, null);
			else
				$this->set($field, $object);

			return $this;
		}

		public function setBoolean($field, $value = false)
		{
			try {
				Assert::isTernaryBase($value);
				$this->set($field, $value);
			} catch (WrongArgumentException $e) {/*_*/}

			return $this;
		}
		
		/**
		 * Adds values from associative array.
		**/
		public function arraySet($fields)
		{
			Assert::isArray($fields);
			
			$this->fields = array_merge($this->fields, $fields);
			
			return $this;
		}
	}
?>