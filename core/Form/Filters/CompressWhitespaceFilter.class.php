<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Replaces multiple adjacent whitespace by one
	 * 
	 * @see RegulatedPrimitive::addImportFilter()
	 * 
	 * @ingroup Filters
	**/
	final class CompressWhitespaceFilter
		extends BaseFilter
		implements Instantiatable
	{
		/**
		 * @return CompressWhitespaceFilter
		**/
		public static function me()
		{
			return Singleton::getInstance('CompressWhitespaceFilter');
		}
		
		public function apply($value)
		{
			return preg_replace('/[ \t]+/', ' ', $value);
		}
	}
?>