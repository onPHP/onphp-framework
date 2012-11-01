<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Factory for Filtrator implementations.
	 * 
	 * @ingroup Form
	**/
	namespace Onphp;

	final class Filter extends StaticFactory
	{
		/**
		 * @return \Onphp\FilterChain
		**/
		public static function textImport()
		{
			return
				FilterChain::create()->
					add(Filter::stripTags())->
					add(Filter::trim());
		}
		
		/**
		 * @return \Onphp\FilterChain
		**/
		public static function chain()
		{
			return new FilterChain();
		}
		
		/**
		 * @return \Onphp\HashFilter
		**/
		public static function hash($binary = false)
		{
			return HashFilter::create($binary);
		}
		
		/**
		 * @return \Onphp\PCREFilter
		**/
		public static function pcre()
		{
			return PCREFilter::create();
		}
		
		/**
		 * @return \Onphp\TrimFilter
		**/
		public static function trim()
		{
			return TrimFilter::create();
		}
		
		/**
		 * @return \Onphp\CropFilter
		**/
		public static function crop()
		{
			return CropFilter::create();
		}
		
		/**
		 * @return \Onphp\StripTagsFilter
		**/
		public static function stripTags()
		{
			return StripTagsFilter::create();
		}
		
		/**
		 * @return \Onphp\LowerCaseFilter
		**/
		public static function lowerCase()
		{
			return Singleton::getInstance('\Onphp\LowerCaseFilter');
		}
		
		/**
		 * @return \Onphp\UpperCaseFilter
		**/
		public static function upperCase()
		{
			return Singleton::getInstance('\Onphp\UpperCaseFilter');
		}
		
		/**
		 * @return \Onphp\HtmlSpecialCharsFilter
		**/
		public static function htmlSpecialChars()
		{
			return Singleton::getInstance('\Onphp\HtmlSpecialCharsFilter');
		}
		
		/**
		 * @return \Onphp\NewLinesToBreaks
		**/
		public static function nl2br()
		{
			return Singleton::getInstance('\Onphp\NewLinesToBreaks');
		}
		
		/**
		 * @return \Onphp\UrlEncodeFilter
		**/
		public static function urlencode()
		{
			return Singleton::getInstance('\Onphp\UrlEncodeFilter');
		}
		
		/**
		 * @return \Onphp\UrlDecodeFilter
		**/
		public static function urldecode()
		{
			return Singleton::getInstance('\Onphp\UrlDecodeFilter');
		}
		
		/**
		 * @return \Onphp\UnixToUnixDecode
		**/
		public static function uudecode()
		{
			return Singleton::getInstance('\Onphp\UnixToUnixDecode');
		}
		
		/**
		 * @return \Onphp\UnixToUnixEncode
		**/
		public static function uuencode()
		{
			return Singleton::getInstance('\Onphp\UnixToUnixEncode');
		}
		
		/**
		 * @return \Onphp\StringReplaceFilter
		**/
		public static function replaceSymbols($search = null, $replace = null)
		{
			return StringReplaceFilter::create($search, $replace);
		}
		
		/**
		 * @return \Onphp\SafeUtf8Filter
		**/
		public static function safeUtf8()
		{
			return Singleton::getInstance('\Onphp\SafeUtf8Filter');
		}
	}
?>