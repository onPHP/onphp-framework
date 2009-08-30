<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Chained Filtrator.
	 * 
	 * @ingroup Form
	**/
	class FilterChain implements Filtrator
	{
		protected $chain = array();

		public static function create()
		{
			return new self;
		}
		
		public function add(Filtrator $filter)
		{
			$this->chain[] = $filter;
			return $this;
		}

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