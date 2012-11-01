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

	final class GoogleChartLine extends GoogleChartPiece
	{
		private $style 		= null;
		private $labelStyle = null;
		
		/**
		 * @return \Onphp\GoogleChartLine
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return \Onphp\GoogleChartLine
		**/
		public function setValue(/* GoogleChartDataSet */ $value)
		{
			return parent::setValue($value);
		}
		
		/**
		 * @return \Onphp\GoogleChartLine
		**/
		public function setStyle(ChartLineStyle $style)
		{
			$this->style = $style;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\ChartLineStyle
		**/
		public function getStyle()
		{
			return $this->style;
		}
		
		/**
		 * @return \Onphp\GoogleChartLine
		**/
		public function setLabelStyle(ChartLabelStyle $style)
		{
			$this->labelStyle = $style;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\GoogleChartLabelStyle
		**/
		public function getLabelStyle()
		{
			return $this->labelStyle;
		}
	}
?>