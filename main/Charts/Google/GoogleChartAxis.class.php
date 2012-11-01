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

	final class GoogleChartAxis
	{
		private $type = null;
		
		private $range = null;
		
		private $label = null;
		
		private $interval = null;
		
		/**
		 * @return \Onphp\GoogleChartAxis
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
		 * @return \Onphp\GoogleChartAxisType
		**/
		public function getType()
		{
			return $this->type;
		}
		
		/**
		 * @return \Onphp\GoogleChartAxis
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
		 * @return \Onphp\IntegerSet
		**/
		public function getRange()
		{
			return $this->range;
		}
		
		/**
		 * @return \Onphp\GoogleChartAxis
		**/
		public function setLabel(GoogleChartAxisLabel $label)
		{
			$this->label = $label;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\GoogleChartAxisLabel
		**/
		public function getLabel()
		{
			return $this->label;
		}
		
		/**
		 * @return \Onphp\GoogleChartAxis
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
?>