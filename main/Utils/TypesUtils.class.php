<?php
/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Utils
 **/
class TypesUtils extends StaticFactory
{
    const SIGNED_MAX = 2147483647;
    const UNSIGNED_MAX = 4294967295;

    public static function signedToUnsigned($signedInt)
    {
        if ($signedInt < 0) {
            return $signedInt + self::UNSIGNED_MAX + 1;
        } else {
            return $signedInt;
        }
    }

    public static function unsignedToSigned($unsignedInt)
    {
        if ($unsignedInt > self::SIGNED_MAX) {
            return $unsignedInt - self::UNSIGNED_MAX - 1;
        } else {
            return $unsignedInt;
        }
    }
}

