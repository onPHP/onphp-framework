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

	/**
	 * Chained Filtrator.
	 * 
	 * @ingroup Form
	**/
	final class FilterChain implements Filtrator
	{
		private $chain = array();

		/**
		 * @return FilterChain
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return FilterChain
		**/
		public function add(Filtrator $filter)
		{
			$this->chain[] = $filter;
			return $this;
		}

		/**
		 * @return FilterChain
		**/
		public function dropAll()
		{
			$this->chain = array();
			return $this;
		}

		public function apply($value)
		{
			foreach ($this->chain as &$filter)
				$value = $filter->apply($value);

			return $value;
		}
	}
?>