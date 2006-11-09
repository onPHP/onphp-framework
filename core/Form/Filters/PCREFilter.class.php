<?php
/***************************************************************************
 *   Copyright (C) 2005 by Sveta Smirnova                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Filters
	**/
	class PCREFilter implements Filtrator
	{
		private $search 	= null;
		private $replace	= null;
		
		/**
		 * @return PCREFilter
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return PCREFilter
		**/
		public function setExpression($search, $replace)
		{
			$this->search = $search;
			$this->replace = $replace;
			
			return $this;
		}
		
		public function apply($value)
		{
			return preg_replace($this->search, $this->replace, $value);
		}
	}
?>