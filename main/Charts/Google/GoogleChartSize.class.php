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
	namespace Onphp;

	final class GoogleChartSize extends BaseGoogleChartParameter
	{
		protected $name = 'chs';
		
		private $width = null;
		private $height = null;
		
		/**
		 * @return \Onphp\GoogleChartSize
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return \Onphp\GoogleChartSize
		**/
		public function setWidth($width)
		{
			Assert::isPositiveInteger($width);
			
			$this->width = $width;
			
			return $this;
		}
		
		public function getWidth()
		{
			return $this->width;
		}
		
		/**
		 * @return \Onphp\GoogleChartSize
		**/
		public function setHeight($height)
		{
			Assert::isPositiveInteger($height);
			
			$this->height = $height;
			
			return $this;
		}
		
		public function getHeight()
		{
			return $this->height;
		}
		
		public function toString()
		{
			return $this->name.'='.$this->width.'x'.$this->height;
		}
	}
?>