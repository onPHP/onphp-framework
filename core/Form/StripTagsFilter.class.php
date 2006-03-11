<?php
/***************************************************************************
 *   Copyright (C) 2005 by Anton E. Lebedevich                             *
 *   noiselist@pochta.ru                                                   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @see RegulatedPrimitive::addImportFilter()
	 * 
	 * @ingroup Filters
	**/
	class StripTagsFilter implements Filtrator
	{
		private $exclude = null;
		
		public static function create()
		{
			return new self;
		}
		
		public function setAllowableTags($exclude)
		{
			if (null !== $exclude)
				Assert::isString($exclude);
			
			$this->exclude = $exclude;
			
			return $this;
		}
		
		public function apply($value)
		{
			return strip_tags($value, $this->exclude);
		}
	}
?>