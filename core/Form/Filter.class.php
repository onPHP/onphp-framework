<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Factory for Filtrator implementations.
	**/
	final class Filter /* Factory */
	{
		public static function textImport()
		{
			return 
				FilterChain::create()->
					add(Filter::trim())->
					add(Filter::stripTags());
		}
		
		public static function chain()
		{
			return new FilterChain();
		}
		
		public static function hash($binary = false)
		{
			return HashFilter::create($binary);
		}

		public static function pcre()
		{
			return PCREFilter::create();
		}

		public static function trim()
		{
			return Singletone::getInstance('TrimFilter');
		}

		public static function stripTags()
		{
			return Singletone::getInstance('StripTagsFilter');
		}

		public static function htmlSpecialChars()
		{
			return Singletone::getInstance('HtmlSpecialCharsFilter');
		}
	}
?>