<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Sveta Smirnova                             *
 *   sveta@microbecal.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Contains collection of different functions usefull for auto-testing
	 * 
	**/
	class SimpleTestUtils
	{
		/**
		 * Calls $function with every pair of key => value from $paramsArray
		 * 
		 * @param	mixed 	callable by call_user_func string or array
		 * @param	array	array, contains pairs for preview param passing
		 * @access	public
		 * @return	void	can be change later
		**/
		public static function callForEachPair($function, $paramsArray)
		{
			foreach ($paramsArray as $key => $value) {
				call_user_func($function, $key, $value);
			}
		}
	}
?>