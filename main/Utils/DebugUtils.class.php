<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Utils
	**/
	final class DebugUtils extends StaticFactory
	{
		public static function el($vr, $prefix = null)
		{
			if ($prefix === null) {
				$trace = debug_backtrace();
				$prefix = basename($trace[0]['file']).':'.$trace[0]['line'];
			}
			
			error_log($prefix.": ".var_export($vr, true));
		}
		
		public static function ev($vr, $prefix = null)
		{
			if ($prefix === null) {
				$trace = debug_backtrace();
				$prefix = basename($trace[0]['file']).':'.$trace[0]['line'];
			}
			
			echo '<pre>'.$prefix.": ".htmlspecialchars(var_export($vr, true)).'</pre>';
		}

		public static function ec($vr, $prefix = null)
		{
			if ($prefix === null) {
				$trace = debug_backtrace();
				$prefix = basename($trace[0]['file']).':'.$trace[0]['line'];
			}
			
			echo "\n".$prefix.": ".var_export($vr, true)."\n";
		}
		
		public static function eq(Query $query, $prefix = null)
		{
			if ($prefix === null) {
				$trace = debug_backtrace();
				$prefix = basename($trace[0]['file']).':'.$trace[0]['line'];
			}
			
			error_log(
				$prefix.": ".$query->toString()
			);
		}
		
		public static function microtime()
		{
			list($usec, $sec) = explode(" ", microtime(), 2);
			return ((float) $usec + (float) $sec);		
		}
	}
?>