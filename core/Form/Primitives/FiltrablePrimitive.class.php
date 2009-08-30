<?php
/****************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich, Konstantin V. Arkhipov *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 3 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/

	/**
	 * Basis for Primitives which can be filtered.
	 * 
	 * @ingroup Primitives
	**/
	abstract class FiltrablePrimitive extends RangedPrimitive
	{
		private $importFilter	= null;
		private $displayFilter 	= null;

		public function __construct($name)
		{
			parent::__construct($name);
			
			$this->displayFilter = new FilterChain();
			$this->importFilter = new FilterChain();
		}

		public function addDisplayFilter(Filtrator $filter)
		{
			$this->displayFilter->add($filter);
			
			return $this;
		}

		public function dropDisplayFilters()
		{
			$this->displayFilter->dropAll();
			
			return $this;
		}

		public function getDisplayValue()
		{
			return $this->displayFilter->apply($this->getActualValue());
		}

		public function addImportFilter(Filtrator $filter)
		{
			$this->importFilter->add($filter);
			
			return $this;
		}

		public function dropImportFilters()
		{
			$this->importFilter->dropAll();
			
			return $this;
		}
		
		public function getImportFilter()
		{
			return $this->importFilter;
		}

		protected function selfFilter()
		{
			$this->value = $this->importFilter->apply($this->value);

			return $this;
		}
	}
?>