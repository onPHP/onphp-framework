<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Factory for Filtrator implementations.
	 * 
	 * @ingroup Form
	**/
	final class Filter extends StaticFactory
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
			return Singleton::getInstance('TrimFilter');
		}

		public static function stripTags()
		{
			return StripTagsFilter::create();
		}

		public static function htmlSpecialChars()
		{
			return Singleton::getInstance('HtmlSpecialCharsFilter');
		}
		
		public static function urlencode()
		{
			return Singleton::getInstance('UrlEncodeFilter');
		}
		
		public static function urldecode()
		{
			return Singleton::getInstance('UrlDecodeFilter');
		}
		
		public static function replaceSymbols($search = null, $replace = null)
		{
			return StringReplaceFilter::create($search, $replace);
		}
		
		/// @deprecated by StringReplaceFilter
		public static function removeSymbols()
		{
			return Singleton::getInstance('RemoveSymbolsFilter');
		}
	}
?>