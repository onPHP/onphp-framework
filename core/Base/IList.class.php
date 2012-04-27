<?php
	/**
	 * @author Artem Naumenko
	 * @copyright (C) 2012-2012 by Artem Naumenko
	 */
	 
	interface IList extends Iterator, ArrayAccess
	{
		/**
		 * Push new list item to the tail of list
		 * 
		 * @return IList 
		 */
		public function append($value);
		
		/**
		 * Push new list item to the head of list
		 * 
		 * @return IList 
		 */
		public function prepend($value);
		
		/**
		 * Returns count of items stored in list
		 * 
		 * @return int 
		 */
		public function count();
		
		/**
		 * Trims $length items starting from @start
		 * 
		 * @return IList 
		 */
		public function trim($start, $length);
		
		/**
		 * Truncates list
		 * 
		 * @return IList 
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
		 * @return IList 
		 */
		public function set($index, $value);
		
		/**
		 * Returns sublist from $start to $start+$length
		 * 
		 * @return array 
		 */
		public function range($start, $length);
	}
