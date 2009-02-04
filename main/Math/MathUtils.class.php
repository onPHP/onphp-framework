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
/* $Id$ */

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
		
		public static function makeCartesianProduct($arrays)
		{
			$result = array();
			
			$size = (sizeof($arrays) > 0) ? 1 : 0;
			
			foreach ($arrays as $array)
				$size *= sizeof($array);
			
			for ($i = 0; $i < $size; $i++) {
				$result[$i] = array();
				
				for ($j = 0; $j < sizeof($arrays); $j++) {
           			array_push($result[$i], current($arrays[$j]));
				}
				
				for ($j = (sizeof($arrays) - 1); $j >= 0; $j--) {
					if (next($arrays[$j])) {
               			break;
           			} else
               			reset($arrays[$j]);
				}
			}
			
			return $result;
		}
	}
?>