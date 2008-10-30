<?php
/***************************************************************************
 *   Copyright (C) 2008 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup GoogleChart
	**/
	final class GoogleChartAxisType extends Enumeration implements Stringable
	{
		const X	= 0x1;
		const Y	= 0x2;
		const R	= 0x3; // aka right y
		
		protected $names = array(
			self::X	=> 'x',
			self::Y	=> 'y',
			self::R	=> 'r'
		);
		
		private $paramName = 'chxt';
		
		public function toString()
		{
			return $this->paramName.'='.$this->name;
		}
	}
?>