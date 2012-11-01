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

	final class ChartLineStyle
	{
		private $thickness 			= 1;
		private $lineSegmentLength 	= 1;
		private $blankSegmentLength = 0;
		
		/**
		 * @return \Onphp\ChartLineStyle
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return \Onphp\ChartLineStyle
		**/
		public function setThickness($size)
		{
			$this->thickness = $size;
			
			return $this;
		}
		
		public function getThickness()
		{
			return $this->thickness;
		}
		
		/**
		 * @return \Onphp\ChartLineStyle
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
		
		/**
		 * @return \Onphp\ChartLineStyle
		**/
		public function setBlankSegmentLength($length)
		{
			$this->blankSegmentLength = $length;
			
			return $this;
		}
		
		public function getBlankSegmentLength()
		{
			return $this->blankSegmentLength;
		}
		
		public function toString()
		{
			return
				$this->thickness
				.','.$this->lineSegmentLength
				.','.$this->blankSegmentLength;
		}
	}
?>