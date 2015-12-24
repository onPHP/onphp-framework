<?php
/***************************************************************************
 *   Copyright (C) 2009 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Used for on-fly detection and turning UTF16 into UTF8.
 *
 * Normally, you should not use this class. There are a little amount of
 * platforms with broken unicode implementations, and this filter tries to
 * detect them and fix their bug.
 *
 * Not working for UTF-16LE, though.
 *
 * @ingroup Filters
 **/
final class Utf16ConverterFilter extends BaseFilter
{
    /**
     * @return Utf16ConverterFilter
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
        if (
            mb_check_encoding($value, 'UTF-16')
            && mb_substr_count($value, "\000") > 0
        ) {
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-16');
        }

        return $value;
    }
}
