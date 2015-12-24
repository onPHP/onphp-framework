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
 * Decode a uuencoded string.
 *
 * @ingroup Filters
 **/
final class UnixToUnixDecode extends BaseFilter
{
    /**
     * @return UnixToUnixDecode
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
        return convert_uudecode($value);
    }
}