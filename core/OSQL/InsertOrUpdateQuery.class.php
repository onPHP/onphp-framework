<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov                     *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
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
		
		public function lazySet($field, /* Identifiable */ $object = null)
		{
			if ($object instanceof Identifiable)
				$this->set($field, $object->getId());
			elseif (is_null($object))
				$this->set($field, null);

			return $this;
		}
		
		public function setBoolean($field, $value = false)
		{
			if (true === $value)
				return $this->set($field, 'true');
			else
				return $this->set($field, 'false');
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