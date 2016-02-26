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
class Filter
{
    /**
     * @return FilterChain
     **/
    public function textImport()
    {
        return
            (new FilterChain())
                ->add(Filter::stripTags())
                ->add(Filter::trim());
    }

    /**
     * @return StripTagsFilter
     **/
    public function stripTags() : StripTagsFilter
    {
        return new StripTagsFilter();
    }

    /**
     * @return TrimFilter
     **/
    public function trim() : TrimFilter
    {
        return new TrimFilter();
    }

    /**
     * @return FilterChain
     **/
    public function chain() : FilterChain
    {
        return new FilterChain();
    }

    /**
     * @return HashFilter
     **/
    public function hash($binary = false) : HashFilter
    {
        return new HashFilter($binary);
    }

    /**
     * @return PCREFilter
     **/
    public function pcre() : PCREFilter
    {
        return new PCREFilter();
    }

    /**
     * @return CropFilter
     **/
    public function crop() : CropFilter
    {
        return new CropFilter();
    }

    /**
     * @return LowerCaseFilter
     **/
    public function lowerCase() : LowerCaseFilter
    {
        return Singleton::getInstance('LowerCaseFilter');
    }

    /**
     * @return UpperCaseFilter
     **/
    public function upperCase() : UpperCaseFilter
    {
        return Singleton::getInstance('UpperCaseFilter');
    }

    /**
     * @return HtmlSpecialCharsFilter
     **/
    public function htmlSpecialChars() : HtmlSpecialCharsFilter
    {
        return Singleton::getInstance('HtmlSpecialCharsFilter');
    }

    /**
     * @return NewLinesToBreaks
     **/
    public function nl2br() : NewLinesToBreaks
    {
        return Singleton::getInstance('NewLinesToBreaks');
    }

    /**
     * @return UrlEncodeFilter
     **/
    public function urlencode() : UrlEncodeFilter
    {
        return Singleton::getInstance('UrlEncodeFilter');
    }

    /**
     * @return UrlDecodeFilter
     **/
    public function urldecode() : UrlDecodeFilter
    {
        return Singleton::getInstance('UrlDecodeFilter');
    }

    /**
     * @return UnixToUnixDecode
     **/
    public function uudecode() : UnixToUnixDecode
    {
        return Singleton::getInstance('UnixToUnixDecode');
    }

    /**
     * @return UnixToUnixEncode
     **/
    public function uuencode() : UnixToUnixEncode
    {
        return Singleton::getInstance('UnixToUnixEncode');
    }

    /**
     * @return StringReplaceFilter
     **/
    public function replaceSymbols($search = null, $replace = null) : StringReplaceFilter
    {
        return new StringReplaceFilter($search, $replace);
    }

    /**
     * @return SafeUtf8Filter
     **/
    public function safeUtf8() : SafeUtf8Filter
    {
        return Singleton::getInstance('SafeUtf8Filter');
    }
}