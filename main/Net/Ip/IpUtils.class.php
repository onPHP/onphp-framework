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
 * @ingroup Ip
 **/
class IpUtils extends StaticFactory
{
    public static function makeRanges(array $ips)
    {
        $ipsAsIntegers = [];

        foreach ($ips as $ip) {
            $ipsAsIntegers[] = ip2long($ip);
        }

        sort($ipsAsIntegers);

        $size = count($ipsAsIntegers);

        $ranges = [];

        $j = 0;

        $ranges[$j][] = long2ip($ipsAsIntegers[0]);

        for ($i = 1; $i < $size; ++$i) {
            if ($ipsAsIntegers[$i] != $ipsAsIntegers[$i - 1] + 1) {
                $ranges[++$j][] = long2ip($ipsAsIntegers[$i]); // start new range
            } else {
                $ranges[$j][] = long2ip($ipsAsIntegers[$i]);
            }
        }

        return $ranges;
    }
}

