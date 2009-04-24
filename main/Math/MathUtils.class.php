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
	 * @ingroup Math
	**/
	final class MathUtils extends StaticFactory
	{
		/**
		 * Fisher Yates shuffle algorithm implementation
		**/
		public static function fisherYatesShuffle(&$elts)
		{
			$num = count($elts);
			
			for ($i = $num - 1; $i > 0; --$i) {
				$j = mt_rand(0, $i);
				
				$tmp		= $elts[$i];
				$elts[$i]	= $elts[$j];
				$elts[$j]	= $tmp;
			}
		}
		
		public static function makeCartesianProduct(
			$arrays, $generateHash = false
		)
		{
			$result = array();
			
			$size = (sizeof($arrays) > 0) ? 1 : 0;
			
			foreach ($arrays as $array)
				$size *= sizeof($array);
			
			$keys = array_keys($arrays);
			
			foreach ($keys as $key)
				$tmpArrays[] = $arrays[$key];
			
			for ($i = 0; $i < $size; $i++) {
				$result[$i] = array();
				
				for ($j = 0; $j < sizeof($tmpArrays); $j++) {
           			$result[$i][$keys[$j]] = current($tmpArrays[$j]);
				}
				
				if ($generateHash)
					$result[$i]['hash'] = md5(implode('_', $result[$i]));
				
				for ($j = (sizeof($tmpArrays) - 1); $j >= 0; $j--) {
					if (next($tmpArrays[$j])) {
               			break;
           			} else
               			reset($tmpArrays[$j]);
				}
			}
			
			return $result;
		}
		
		public static function randFloat($min, $max)
		{
			return ($min + lcg_value() * (abs($max - $min)));
		}
		
		public static function alignByBase($value, $base, $ceil = false)
		{
			$function = $ceil ? 'ceil' : 'floor';
			
			return $function($value / $base) * $base;
		}
	}
?>