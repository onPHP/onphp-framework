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

	final class GoogleChartSolidFillCollection extends BaseGoogleChartParameter
	{
		private $fillers = array();
		
		/**
		 * @return \Onphp\GoogleChartSolidFillCollection
		**/
		public static function create()
		{
			return new self;
		}
		
		public function addFiller(GoogleChartSolidFillType $type, Color $color)
		{
			$this->fillers[] =
				GoogleChartSolidFill::create($type)->
				setColor($color);
			
			return $this;
		}
		
		public function hasFillers()
		{
			return !empty($this->fillers);
		}
		
		public function toString()
		{
			$fillerString = GoogleChartSolidFill::getParamName().'=';
			
			foreach ($this->fillers as $filler)
				$fillers[] = $filler->toString();
			
			return $fillerString.implode('|', $fillers);
		}
	}
?>