<?php
/***************************************************************************
 *   Copyright (C) 2005 by Sveta Smirnova                                  *
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
	 * Container of asserts
	**/
	class Suite
	{
		/**
		 * Process query
		 * 
		 * @param	mixed		expected result
		 * @param	mixed		actual result
		 * @access	public
		 * @return	boolean		true if $expected === $actual, false otherwise
		**/
		public function equals($expected, $actual)
		{
			return $expected === $actual;
		}
	}
?>