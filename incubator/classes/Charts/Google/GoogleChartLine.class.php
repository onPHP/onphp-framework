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
	final class GoogleChartLine
	{
		private $title	= null;
		private $color	= null;
		private $value	= null;
		
		/**
		 * @return GoogleChartLine
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return GoogleChartLine
		**/
		public function setTitle($title)
		{
			$this->title = $title;
			
			return $this;
		}
		
		public function getTitle()
		{
			return $this->title;
		}
		
		/**
		 * @return GoogleChartLine
		**/
		public function setColor(Color $color)
		{
			$this->color = $color;
			
			return $this;
		}
		
		/**
		 * @return Color
		**/
		public function getColor()
		{
			return $this->color;
		}
		
		/**
		 * @return GoogleChartLine
		**/
		public function setValue(GoogleChartDataSet $set)
		{
			$this->value = $set;
			
			return $this;
		}
		
		public function getValue()
		{
			return $this->value;
		}
	}
?>