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

			$ranges[$j][] = long2ip($ipsAsIntegers[0]);

			for ($i = 1; $i < $size; ++$i) {
				if ($ipsAsIntegers[$i] != $ipsAsIntegers[$i - 1] + 1) {
					$ranges[++$j][] = long2ip($ipsAsIntegers[$i]); // start new range
				} else
					$ranges[$j][] = long2ip($ipsAsIntegers[$i]);
			}
			
			return $ranges;
		}

		public static function checkCIDR($ip, $cidr) {
			if( !preg_match('@(\d+\.\d+\.\d+\.\d+)(?:/(\d+))?@mi', $cidr, $matches) ) {
				throw new WrongArgumentException('CIDR is not valid');
			}
			$net = $matches[1];
			$mask = isset($matches[2]) ? $matches[2] : 32;
			// 2 long
			$int_ip = ip2long($ip);
			$int_net = ip2long($net);
			$int_mask = ~((1 << (32 - $mask)) - 1);
			// bitwise AND
			$ip_mask = $int_ip & $int_mask;
			// check
			return ($ip_mask == $int_net);
		}
	}
?>