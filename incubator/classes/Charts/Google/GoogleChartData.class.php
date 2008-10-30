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
	final class GoogleChartData extends BaseGoogleChartData
	{
		protected $name = 'chd';
		
		private $sets = array();
		
		private $dataScaling = true;
		
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
		public function setDataScaling($scale = true)
		{
			$this->dataScaling = (true === $scale);
			
			return $this;
		}
		
		public function withDataScaling()
		{
			return $this->dataScaling;
		}
		
		/**
		 * @return GoogleChartData
		**/
		public function addDataSet(GoogleChartDataSet $set)
		{
			$this->sets[] = $set;
			
			return $this;
		}
		
		public function getDataSetByIndex($index)
		{
			if (!isset($this->sets[$index]))
				throw new WrongArgumentException(
					"Dataset with index {$index} not found"
				);
			
			return $this->sets[$index];
		}
		
		public function toString()
		{
			Assert::isNotNull($this->encoding, 'Data encdoing Required.');
			
			$boundString = null;
			
			$dataStrings = $bounds = array();
			
			if ($this->dataScaling)
				$boundString = '&'.GoogleChartDataScaling::getParamName().'=';
			
			foreach ($this->sets as $set) {
				$this->encoding->setMaxValue($set->getMax() + 1);
				$dataStrings[] = $this->encoding->encode($set);
				
				if ($this->dataScaling)
					$bounds[] = $set->getMin().','.$set->getMax();
			}
			
			if ($this->dataScaling)
				$boundString .= implode(',', $bounds);
			
			$dataString = implode('|', $dataStrings);
			
			$encodingString = $this->encoding->toString();
			
			return $this->name.'='.$encodingString.$dataString.$boundString;
		}
	}
?>