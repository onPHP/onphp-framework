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
 * Inserts HTML line breaks before all newlines in a string.
 *
 * @ingroup Filters
 **/
final class NewLinesToBreaks extends BaseFilter
{
    /**
     * @return NewLinesToBreaks
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
        return nl2br($value);
    }
}
