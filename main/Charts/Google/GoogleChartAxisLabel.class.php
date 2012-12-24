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
	final class GoogleChartAxisLabel extends GoogleChartLabel
	{
		protected static $paramName = 'chxl';
		
		/**
		 * @return GoogleChartLabel
		**/
		public static function create()
		{
			return new self;
		}
		
		public static function getParamName()
		{
			return self::$paramName;
		}
		
		public function toString()
		{
			$labels = implode('|', $this->labels);
			
			return $labels;
		}
	}
