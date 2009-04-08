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
	final class GoogleChartDataSet
	{
		private $data = array();
		
		private $minMax = null;
		
		private $base = null;
		
		/**
		 * @return GoogleChartDataSet
		**/
		public static function create()
		{
			return new self;
		}
		
		public function __construct()
		{
			$this->minMax = FloatRange::create(0, 0);
		}
		
		/**
		 * @return GoogleChartDataSet
		**/
		public function setData(array $data)
		{
			$this->data = $data;
			
			return $this;
		}
		
		public function getData()
		{
			return $this->data;
		}
		
		/**
		 * @return GoogleChartDataSet
		**/
		public function addElement($element)
		{
			$this->data[] = $element;
			
			return $this;
		}
		
		public function setBase($base)
		{
			$this->base = $base;
			
			// reset
			$this->minMax->setMax(0);
			
			return $this;
		}
		
		public function getBase()
		{
			return $this->base;
		}
		
		public function getSize()
		{
			return count($this->data);
		}
		
		public function getMin()
		{
			return $this->minMax->getMin();
		}
		
		public function setMax($max)
		{
			$this->minMax->setMax($max);
			
			return $this;
		}
		
		public function getMax()
		{
			if ($this->minMax->getMax() == 0)
				$this->calculateMax();
			
			return $this->minMax->getMax();
		}
		
		public function getMinMax()
		{
			return $this->minMax;
		}
		
		public function getStepSize()
		{
			Assert::isTrue($this->base > 0);
			
			return $this->getMax() / $this->base;
		}
		
		/**
		 * @return GoogleChartDataSet
		**/
		private function calculateMax()
		{
			$maxValue = max($this->data);
			
			if ($this->base)
				$maxValue =
					MathUtils::alignByBase($maxValue, $this->base, true);
			
			$this->minMax->setMax($maxValue);
			
			return $this;
		}
	}
?>