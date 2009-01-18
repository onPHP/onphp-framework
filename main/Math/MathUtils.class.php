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
	}
?>