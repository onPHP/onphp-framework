<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * HTML Special Characters replacer.
 *
 * @ingroup Filters
 **/
final class HtmlSpecialCharsFilter extends BaseFilter
{
    /**
     * @return HtmlSpecialCharsFilter
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
        return htmlspecialchars($value);
    }
}
