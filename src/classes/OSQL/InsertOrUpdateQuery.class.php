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

	abstract class InsertOrUpdateQuery extends Query
	{
		protected $table	= null;
		protected $fields	= array();

		public function set($field, $value = null)
		{
			$this->fields[$field] = $value;
			
			return $this;
		}
		
		public function setBoolean($var, $val = false)
		{
			if (true === $val)
				return $this->set($var, 'true');
			else
				return $this->set($var, 'false');
		}

		/**
		 * Adds values from associative array
		 * 
		 * @param	array	associative array('name' => 'value')
		 * @access	public
		 * @return	InsertQuery
		**/
		public function arraySet($fields)
		{
			Assert::isArray($fields);

			$this->fields = array_merge($this->fields, $fields);

			return $this;
		}
	}
?>