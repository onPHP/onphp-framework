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
	final class GoogleChartSolidFill extends BaseGoogleChartParameter
	{
		protected static $paramName = 'chf';
		
		private $type 	= null;
		private $color 	= null;
		
		/**
		 * @return GoogleChartSolidFill
		**/
		public static function create(GoogleChartSolidFillType $type)
		{
			return new self($type);
		}
		
		public function __construct(GoogleChartSolidFillType $type)
		{
			$this->type = $type;
		}
		
		/**
		 * @return GoogleChartSolidFill
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
		
		public function toString()
		{
			Assert::isNotNull($this->color, 'Color parameter required!');
			
			return
				$this->type->toString()
				.',s'
				.','.$this->color->toString();
		}
		
		
		public static function getParamName()
		{
			return self::$paramName;
		}
	}
