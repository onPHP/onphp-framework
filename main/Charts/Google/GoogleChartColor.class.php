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

	/**
	 * @ingroup GoogleChart
	**/
	final class GoogleChartColor extends BaseGoogleChartParameter
	{
		protected $name = 'chco';
		
		private $colors = array();
		
		/**
		 * @return GoogleChartColor
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return GoogleChartColor
		**/
		public function addColor(Color $color)
		{
			$this->colors[] = $color;
			
			return $this;
		}
		
		public function toString()
		{
			$queryString = "{$this->name}=";
			
			Assert::isNotEmptyArray($this->colors);
			
			foreach ($this->colors as $color)
				$queryString .= $color->toString().',';
			
			return rtrim($queryString, ',');
		}
	}
?>