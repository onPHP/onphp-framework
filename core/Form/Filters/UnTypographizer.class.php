<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Filters
 **/
class UnTypographizer extends BaseFilter
{
    /** @var array  */
    private static $symbols =
        [
            '&nbsp;' => ' ',
            ' &lt; ' => ' < ',
            ' &gt; ' => ' > ',
            '&#133;' => '…',
            '&trade;' => '™',
            '&copy;' => '©',
            '&#8470;' => '№',
            '&#151;' => '—',
            '&mdash;' => '—',
            '&laquo;' => '«',
            '&raquo;' => '»',
            '&bull;' => '•',
            '&reg;' => '®',
            '&frac14;' => '¼',
            '&frac12;' => '½',
            '&frac34;' => '¾',
            '&plusmn;' => '±'
        ];

    /**
     * @return UnTypographizer
     **/
    public static function me()
    {
        return Singleton::getInstance(__CLASS__);
    }

    /**
     * @param $value
     * @return string
     */
    public function apply($value) : string
    {
        return strtr($value, self::$symbols);
    }
}
