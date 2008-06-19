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
/* $Id$ */

	/**
	 * Factory for Filtrator implementations.
	 * 
	 * @ingroup Form
	**/
	final class Filter extends StaticFactory
	{
		/**
		 * @return FilterChain
		**/
		public static function textImport()
		{
			return
				FilterChain::create()->
					add(Filter::trim())->
					add(Filter::stripTags());
		}
		
		/**
		 * @return FilterChain
		**/
		public static function chain()
		{
			return new FilterChain();
		}
		
		/**
		 * @return HashFilter
		**/
		public static function hash($binary = false)
		{
			return HashFilter::create($binary);
		}
		
		/**
		 * @return PCREFilter
		**/
		public static function pcre()
		{
			return PCREFilter::create();
		}
		
		/**
		 * @return TrimFilter
		**/
		public static function trim()
		{
			return TrimFilter::create();
		}
		
		/**
		 * @return CropFilter
		**/
		public static function crop()
		{
			return CropFilter::create();
		}
		
		/**
		 * @return StripTagsFilter
		**/
		public static function stripTags()
		{
			return StripTagsFilter::create();
		}
		
		/**
		 * @return LowerCaseFilter
		**/
		public static function lowerCase()
		{
			return Singleton::getInstance('LowerCaseFilter');
		}
		
		/**
		 * @return UpperCaseFilter
		**/
		public static function upperCase()
		{
			return Singleton::getInstance('UpperCaseFilter');
		}
		
		/**
		 * @return HtmlSpecialCharsFilter
		**/
		public static function htmlSpecialChars()
		{
			return Singleton::getInstance('HtmlSpecialCharsFilter');
		}
		
		/**
		 * @return NewLinesToBreaks
		**/
		public static function nl2br()
		{
			return Singleton::getInstance('NewLinesToBreaks');
		}
		
		/**
		 * @return UrlEncodeFilter
		**/
		public static function urlencode()
		{
			return Singleton::getInstance('UrlEncodeFilter');
		}
		
		/**
		 * @return UrlDecodeFilter
		**/
		public static function urldecode()
		{
			return Singleton::getInstance('UrlDecodeFilter');
		}
		
		/**
		 * @return UnixToUnixDecode
		**/
		public static function uudecode()
		{
			return Singleton::getInstance('UnixToUnixDecode');
		}
		
		/**
		 * @return UnixToUnixEncode
		**/
		public static function uuencode()
		{
			return Singleton::getInstance('UnixToUnixEncode');
		}
		
		/**
		 * @return StringReplaceFilter
		**/
		public static function replaceSymbols($search = null, $replace = null)
		{
			return StringReplaceFilter::create($search, $replace);
		}
		
		/**
		 * @return SafeUtf8Filter
		**/
		public static function safeUtf8()
		{
			return Singleton::getInstance('SafeUtf8Filter');
		}
		
		/**
		 * @return Stringizer
		**/
		public static function stringizer()
		{
			return Singleton::getInstance('Stringizer');
		}
	}
?>