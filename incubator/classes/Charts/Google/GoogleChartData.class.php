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

	final class GoogleChartData extends BaseGoogleChartData
	{
		protected $name = 'chd';
		
		private $sets = array();
		
		/**
		 * @return GoogleChartData
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return GoogleChartData
		**/
		public function addDataSet(GoogleChartDataSet $set)
		{
			$this->sets[] = $set;
			
			return $this;
		}
		
		public function toQueryString()
		{
			Assert::isNotNull($this->encoding, 'Data encdoing Required.');
			
			$dataStrings = array();
			
			foreach ($this->sets as $set) {
				$this->encoding->setMaxValue(max($set->getData()) + 1);
				$dataStrings[] = $this->encoding->encode($set);
			}
			
			$dataString = implode('|', $dataStrings);
			
			$encodingString = $this->encoding->toQueryString();
			
			return "{$this->name}={$encodingString}{$dataString}";
		}
	}
?>