<?php
/***************************************************************************
 *   Copyright (C) 2008-2009 by Denis M. Gabaidulin                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup GoogleChart
	**/
	final class GoogleChartType extends Enumeration
	{
		const LINE					= 0x1;
		const TWO_DIMENSIONAL_PIE	= 0x2;
		
		protected $names = array(
			self::LINE					=> 'lc',
			self::TWO_DIMENSIONAL_PIE	=> 'p'
		);
		
		private $paramName = 'cht';
		
		public function toString()
		{
			return $this->paramName.'='.$this->name;
		}
	}
?>