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
	namespace Onphp;

	final class GoogleChartAxisType extends Enumeration
	{
		const X	= 0x1;
		const Y	= 0x2;
		const R	= 0x3; // aka right y
		
		protected $names = array(
			self::X	=> 'x',
			self::Y	=> 'y',
			self::R	=> 'r'
		);
		
		private static $paramName = 'chxt';
		
		public static function getParamName()
		{
			return self::$paramName;
		}
		
		public function toString()
		{
			return $this->name;
		}
	}
?>