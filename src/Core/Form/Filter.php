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

namespace OnPHP\Core\Form;

use OnPHP\Core\Base\Singleton;
use OnPHP\Core\Base\StaticFactory;
use OnPHP\Core\Form\Filters\FilterChain;
use OnPHP\Core\Form\Filters\HashFilter;
use OnPHP\Core\Form\Filters\PCREFilter;
use OnPHP\Core\Form\Filters\TrimFilter;
use OnPHP\Core\Form\Filters\CropFilter;
use OnPHP\Core\Form\Filters\StripTagsFilter;
use OnPHP\Core\Form\Filters\StringReplaceFilter;
use OnPHP\Core\Form\Filters\UnixToUnixDecode;
use OnPHP\Core\Form\Filters\LowerCaseFilter;
use OnPHP\Core\Form\Filters\UpperCaseFilter;
use OnPHP\Core\Form\Filters\HtmlSpecialCharsFilter;
use OnPHP\Core\Form\Filters\NewLinesToBreaks;
use OnPHP\Core\Form\Filters\UrlEncodeFilter;
use OnPHP\Core\Form\Filters\UrlDecodeFilter;
use OnPHP\Core\Form\Filters\UnixToUnixEncode;
use OnPHP\Core\Form\Filters\SafeUtf8Filter;

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
				add(Filter::stripTags())->
				add(Filter::trim());
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
		return LowerCaseFilter::me();
	}

	/**
	 * @return UpperCaseFilter
	**/
	public static function upperCase()
	{
		return UpperCaseFilter::me();
	}

	/**
	 * @return HtmlSpecialCharsFilter
	**/
	public static function htmlSpecialChars()
	{
		return HtmlSpecialCharsFilter::me();
	}

	/**
	 * @return NewLinesToBreaks
	**/
	public static function nl2br()
	{
		return NewLinesToBreaks::me();
	}

	/**
	 * @return UrlEncodeFilter
	**/
	public static function urlencode()
	{
		return UrlEncodeFilter::me();
	}

	/**
	 * @return UrlDecodeFilter
	**/
	public static function urldecode()
	{
		return UrlDecodeFilter::me();
	}

	/**
	 * @return UnixToUnixDecode
	**/
	public static function uudecode()
	{
		return UnixToUnixDecode::me();
	}

	/**
	 * @return UnixToUnixEncode
	**/
	public static function uuencode()
	{
		return UnixToUnixEncode::me();
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
		return SafeUtf8Filter::me();
	}
}
?>