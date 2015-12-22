<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Base for handling Identifiable object's lists.
	 * 
	 * @ingroup onSPL
	**/
	abstract class AbstractList implements ArrayAccess, SimplifiedArrayAccess
	{
		protected $list = array();
		
		public function offsetGet($offset)
		{
			if (isset($this->list[$offset]))
				return $this->list[$offset];
			
			throw new MissingElementException(
				"no object found with index == '{$offset}'"
			);
		}
		
		/**
		 * @return AbstractList
		**/
		public function offsetUnset($offset)
		{
			unset($this->list[$offset]);
			
			return $this;
		}
		
		public function offsetExists($offset)
		{
			return isset($this->list[$offset]);
		}
		
		// SAA goes here
		
		/**
		 * @return AbstractList
		**/
		public function clean()
		{
			$this->list = array();
			
			return $this;
		}
		
		public function isEmpty()
		{
			return ($this->list === array());
		}
		
		public function getList()
		{
			return $this->list;
		}
		
		public function set($name, $var)
		{
			return $this->offsetSet($name, $var);
		}
		
		public function get($name)
		{
			return $this->offsetGet($name);
		}
		
		public function has($name)
		{
			return $this->offsetExists($name);
		}
		
		public function drop($name)
		{
			return $this->offsetUnset($name);
		}
	}
?>