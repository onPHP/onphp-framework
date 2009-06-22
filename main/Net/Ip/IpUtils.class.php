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
	final class IpUtils extends StaticFactory
	{
		public static function makeRanges(array $ips)
		{
			$ipsAsIntegers = array();

			foreach ($ips as $ip)
				$ipsAsIntegers[] = ip2long($ip);

			sort($ipsAsIntegers);
			
			$size = count($ipsAsIntegers);

			$ranges = array();

			$j = 0;

			$ranges[$j][] = $ipsAsIntegers[0];

			for ($i = 1; $i < $size; ++$i) {
				if ($ipsAsIntegers[$i] != $ipsAsIntegers[$i - 1] + 1) {
					$ranges[++$j][] = $ipsAsIntegers[$i]; // start new range
				} else
					$ranges[$j][] = $ipsAsIntegers[$i];
			}

			return $ranges;
		}
	}
?>