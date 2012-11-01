<?php
	/***************************************************************************
	*   Copyright (C) 2012 by Artem Naumenko                                  *
	*                                                                         *
	*   This program is free software; you can redistribute it and/or modify  *
	*   it under the terms of the GNU Lesser General Public License as        *
	*   published by the Free Software Foundation; either version 3 of the    *
	*   License, or (at your option) any later version.                       *
	*                                                                         *
	***************************************************************************/
	
	namespace Onphp;

	interface Listable extends \Iterator, \ArrayAccess, \Countable, \SeekableIterator
	{
		/**
		 * Push new list item to the tail of list
		 *
		 * @return \Onphp\Listable
		 */
		public function append($value);
		
		/**
		 * Push new list item to the head of list
		 *
		 * @return \Onphp\Listable
		 */
		public function prepend($value);
		
		/**
		 * Trims $length items starting from @start
		 *
		 * @return \Onphp\Listable
		 */
		public function trim($start, $length);
		
		/**
		 * Truncates list
		 *
		 * @return \Onphp\Listable
		 */
		public function clear();
		
		/**
		 * @return mixed
		 */
		public function get($index);
		
		/**
		 * Returns the last element of list and removing it
		 *
		 * @return mixed
		 */
		public function pop();
		
		/**
		 * @return \Onphp\Listable
		 */
		public function set($index, $value);
		
		/**
		 * Returns sublist from $start to $start+$length
		 *
		 * @return array
		 */
		public function range($start, $length);
	}
