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

	final class GoogleChartGrid extends BaseGoogleChartParameter
	{
		protected $name = 'chg';
		
		private $horizontalStepSize = 0;
		private $verticalStepSize 	= 0;
		private $lineSegmentLength 	= 0;
		
		/**
		 * @return \Onphp\GoogleChartGrid
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return \Onphp\GoogleChartGrid
		**/
		public function setHorizontalStepSize($size)
		{
			$this->horizontalStepSize = $size;
			
			return $this;
		}
		
		public function getHorizontalStepSize()
		{
			return $this->horizontalStepSize;
		}
		
		/**
		 * @return \Onphp\GoogleChartGrid
		**/
		public function setVerticalStepSize($size)
		{
			$this->verticalStepSize = $size;
			
			return $this;
		}
		
		public function getVerticalStepSize()
		{
			return $this->verticalStepSize;
		}
		
		/**
		 * @return \Onphp\GoogleChartGrid
		**/
		public function setLineSegmentLength($length)
		{
			$this->lineSegmentLength = $length;
			
			return $this;
		}
		
		public function getLineSegmentLength()
		{
			return $this->lineSegmentLength;
		}
		
		public function toString()
		{
			return
				$this->name
				.'='
				.$this->horizontalStepSize
				.','
				.$this->verticalStepSize
				.','
				.$this->lineSegmentLength;
		}
	}
?>