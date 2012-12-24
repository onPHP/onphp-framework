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
	final class GoogleChartLegend extends BaseGoogleChartParameter
	{
		protected $name = 'chdl';
		
		private $items = array();
		private $position = null;
		
		/**
		 * @return GoogleChartLegend
		**/
		public static function create()
		{
			return new self;
		}
		
		public function __construct()
		{
			$this->position =
				GoogleChartLegendPositionType::create(
					GoogleChartLegendPositionType::LEFT
				);
		}
		
		/**
		 * @return GoogleChartLegend
		**/
		public function setPosition(GoogleChartLegendPositionType $type)
		{
			$this->position = $type;
			
			return $this;
		}
		
		/**
		 * @return GoogleChartLegend
		**/
		public function addItem($item)
		{
			$this->items[] = $item;
			
			return $this;
		}
		
		public function toString()
		{
			$items = implode('|', $this->items);
			
			return $this->name.'='.$items.'&'.$this->position->toString();
		}
	}
