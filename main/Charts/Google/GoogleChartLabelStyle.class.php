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

	/**
	 * @ingroup GoogleChart
	**/
	final class GoogleChartLabelStyle
	{
		protected $name = 'chm';
		
		private $styles = array();
		
		/**
		 * @return GoogleChartLabelStyle
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return GoogleChartLabelStyle
		**/
		public function addStyle(ChartLabelStyle $style)
		{
			$this->styles[] = $style;
			
			return $this;
		}
		
		public function hasStyles()
		{
			return !empty($this->styles);
		}
		
		public function toString()
		{
			$queryString = "{$this->name}=";
			
			Assert::isNotEmptyArray($this->styles);
			
			$i = 0;
			
			foreach ($this->styles as $style) {
				$style->setDataSetIndex($i++);
				$queryString .= $style->toString().'|';
			}
			
			return rtrim($queryString, '|');
		}
	}
?>