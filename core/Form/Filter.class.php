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
class Filter extends StaticFactory
{
    /**
     * @return FilterChain
     **/
    public static function textImport()
    {
        return
            (new FilterChain())
                ->add(Filter::stripTags())
                ->add(Filter::trim());
    }

    /**
     * @return StripTagsFilter
     **/
    public static function stripTags() : StripTagsFilter
    {
        return new StripTagsFilter();
    }

    /**
     * @return TrimFilter
     **/
    public static function trim() : TrimFilter
    {
        return new TrimFilter();
    }

    /**
     * @return FilterChain
     **/
    public static function chain() : FilterChain
    {
        return new FilterChain();
    }

    /**
     * @return HashFilter
     **/
    public static function hash($binary = false) : HashFilter
    {
        return new HashFilter($binary);
    }

    /**
     * @return PCREFilter
     **/
    public static function pcre() : PCREFilter
    {
        return new PCREFilter();
    }

    /**
     * @return CropFilter
     **/
    public static function crop() : CropFilter
    {
        return new CropFilter();
    }

    /**
     * @return LowerCaseFilter
     **/
    public static function lowerCase() : LowerCaseFilter
    {
        return Singleton::getInstance('LowerCaseFilter');
    }

    /**
     * @return UpperCaseFilter
     **/
    public static function upperCase() : UpperCaseFilter
    {
        return Singleton::getInstance('UpperCaseFilter');
    }

    /**
     * @return HtmlSpecialCharsFilter
     **/
    public static function htmlSpecialChars() : HtmlSpecialCharsFilter
    {
        return Singleton::getInstance('HtmlSpecialCharsFilter');
    }

    /**
     * @return NewLinesToBreaks
     **/
    public static function nl2br() : NewLinesToBreaks
    {
        return Singleton::getInstance('NewLinesToBreaks');
    }

    /**
     * @return UrlEncodeFilter
     **/
    public static function urlencode() : UrlEncodeFilter
    {
        return Singleton::getInstance('UrlEncodeFilter');
    }

    /**
     * @return UrlDecodeFilter
     **/
    public static function urldecode() : UrlDecodeFilter
    {
        return Singleton::getInstance('UrlDecodeFilter');
    }

    /**
     * @return UnixToUnixDecode
     **/
    public static function uudecode() : UnixToUnixDecode
    {
        return Singleton::getInstance('UnixToUnixDecode');
    }

    /**
     * @return UnixToUnixEncode
     **/
    public static function uuencode() : UnixToUnixEncode
    {
        return Singleton::getInstance('UnixToUnixEncode');
    }

    /**
     * @return StringReplaceFilter
     **/
    public static function replaceSymbols($search = null, $replace = null) : StringReplaceFilter
    {
        return new StringReplaceFilter($search, $replace);
    }

    /**
     * @return SafeUtf8Filter
     **/
    public static function safeUtf8() : SafeUtf8Filter
    {
        return Singleton::getInstance('SafeUtf8Filter');
    }
}