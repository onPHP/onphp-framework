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
 * @ingroup Mobile
 **/
class MobileUtils extends StaticFactory
{
    public static function extractIp(array $headers)
    {
        if (
            (new MobileRequestDetector())->isOperaMini($headers)
            && isset($headers['HTTP_X_FORWARDED_FOR'])
        ) {
            $ips = explode(',', $headers['HTTP_X_FORWARDED_FOR']);

            if ($ips) {
                return trim($ips[count($ips) - 1]);
            }
        } elseif (isset($headers['REMOTE_ADDR'])) {
            return $headers['REMOTE_ADDR'];
        }

        return null;
    }

    public static function extractUserAgent(array $headers)
    {
        if (
            (new MobileRequestDetector())->isOperaMini($headers)
            && isset($headers['HTTP_X_OPERAMINI_PHONE_UA'])
        ) {
            return $headers['HTTP_X_OPERAMINI_PHONE_UA'];
        } elseif (isset($headers['HTTP_USER_AGENT'])) {
            return $headers['HTTP_USER_AGENT'];
        }

        return null;
    }
}

