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
	namespace Onphp;

	final class ChartLabelStyle
	{
		private $type			= null;
		private $color			= null;
		private $dataSetIndex	= null;
		private $size 			= null;
		private $dataPoint		= null;
		
		/**
		 * @return \Onphp\ChartLabelStyle
		**/
		public static function create()
		{
			return new self;
		}
		
		public function __construct()
		{
			$this->color 		= Color::create('000000');
			$this->type 		= GoogleChartLabelStyleNumberType::create();
			$this->size			= 10;
			$this->dataPoint	= -1;
		}
		
		/**
		 * @return \Onphp\ChartLabelStyle
		**/
		public function setType(BaseGoogleChartLabelStyleType $type)
		{
			$this->type = $type;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\BaseGoogleChartLabelStyleType
		**/
		public function getType()
		{
			return $this->type;
		}
		
		/**
		 * @return \Onphp\ChartLabelStyle
		**/
		public function setColor(Color $color)
		{
			$this->color = $color;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\Color
		**/
		public function getColor()
		{
			return $this->color;
		}
		
		public function setDataSetIndex($index)
		{
			Assert::isInteger($index);
			
			$this->dataSetIndex = $index;
			
			return $this;
		}
		
		public function getDataSetIndex()
		{
			return $this->dataSetIndex;
		}
		
		public function setSize($size)
		{
			Assert::isPositiveInteger($size);
			
			$this->size = $size;
			
			return $this;
		}
		
		public function getSize()
		{
			return $this->size;
		}
		
		public function setDataPoint($value)
		{
			$this->dataPoint = $value;
			
			return $this;
		}
		
		public function getDataPoint()
		{
			return $this->dataPoint;
		}
		
		public function toString()
		{
			Assert::isNotNull($this->dataSetIndex);
			Assert::isNotNull($this->size);
			
			return
				$this->type->toString()
				.','.$this->color->toString()
				.','.$this->dataSetIndex
				.','.$this->dataPoint
				.','.$this->size;
		}
	}
?>