<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich                        *
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
	final class TrimFilter implements Filtrator
	{
		private $charlist	= null;
		
		/**
		 * @return TrimFilter
		**/
		public static function create()
		{
			return new self;
		}
		
		public function apply($value)
		{
			return ($this->charlist ? trim($value, $this->charlist) : trim($value));
		}
		
		/**
		 * @return TrimFilter
		**/
		public function setCharlist($charlist)
		{
			$this->charlist = $charlist;
			
			return $this;
		}
	}
?>