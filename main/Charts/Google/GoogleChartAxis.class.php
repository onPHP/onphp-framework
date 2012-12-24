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
	final class GoogleChartAxis
	{
		private $type = null;
		
		private $range = null;
		
		private $label = null;
		
		private $interval = null;
		
		/**
		 * @return GoogleChartAxis
		**/
		public static function create(GoogleChartAxisType $type)
		{
			return new self($type);
		}
		
		public function __construct(GoogleChartAxisType $type)
		{
			$this->type = $type;
		}
		
		/**
		 * @return GoogleChartAxisType
		**/
		public function getType()
		{
			return $this->type;
		}
		
		/**
		 * @return GoogleChartAxis
		**/
		public function setRange(BaseRange $range)
		{
			$this->range = $range;
			
			return $this;
		}
		
		public function hasRange()
		{
			return ($this->range !== null);
		}
		
		/**
		 * @return IntegerSet
		**/
		public function getRange()
		{
			return $this->range;
		}
		
		/**
		 * @return GoogleChartAxis
		**/
		public function setLabel(GoogleChartAxisLabel $label)
		{
			$this->label = $label;
			
			return $this;
		}
		
		/**
		 * @return GoogleChartAxisLabel
		**/
		public function getLabel()
		{
			return $this->label;
		}
		
		/**
		 * @return GoogleChartAxis
		**/
		public function setInterval($interval)
		{
			Assert::isTrue(is_numeric($interval));
			
			$this->interval = $interval;
			
			return $this;
		}
		
		public function getInterval()
		{
			return $this->interval;
		}
	}
