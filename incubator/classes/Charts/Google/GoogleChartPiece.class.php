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
	final class GoogleChartPiece
	{
		private $title 	= null;
		private $color	= null;
		private $value 	= null;
		
		/**
		 * @return GoogleChartPiece
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return GoogleChartPiece
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
		 * @return GoogleChartPiece
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
		 * @return GoogleChartPiece
		**/
		public function setValue($value)
		{
			$this->value = $value;
			
			return $this;
		}
		
		public function getValue()
		{
			return $this->value;
		}
	}
?>