<?php
/***************************************************************************
 *   Copyright (C) 2008-2009 by Denis M. Gabaidulin                        *
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
	final class GoogleChartAxisCollection
	{
		private $axes = array();
		
		/**
		 * @return GoogleChartAxisCollection
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return GoogleChartAxisCollection
		**/
		public function addAxis(GoogleChartAxis $axis)
		{
			$typeId = $axis->getType()->getId();
			
			if (isset($this->axes[$typeId]))
				throw new WrongArgumentException('Axis already exists');
			
			$this->axes[$typeId] = $axis;
			
			return $this;
		}
		
		public function toString()
		{
			$typeString = GoogleChartAxisType::getParamName().'=';
			
			$rangeString = GoogleChartDataRange::getParamName().'=';
			
			$labelsString = null;
			
			$types = $ranges = $labels = array();
			
			$i = 0;
			
			foreach ($this->axes as $axis) {
				$types[] = $axis->getType()->toString();
				
				if ($range = $axis->getRange())
					$ranges[$i] =
						$i
						.','
						.$range->getMin()
						.','
						.$range->getMax();
				
				if ($interval = $axis->getInterval())
					$ranges[$i] .= ','.$interval;
						
				if ($label = $axis->getLabel())
					$labels[$i] = $label;
				
				$i++;
			}
			
			$typeString .= implode(',', $types);
			
			$rangeString.= implode('|', $ranges);
			
			if ($labels) {
				$labelsString = '&'.GoogleChartAxisLabel::getParamName().'=';
				
				foreach ($labels as $axisId => $label) {
					$labelsString .= $axisId.':|'.$label->toString();
				}
			}
			
			return $typeString.'&'.$rangeString.$labelsString;
		}
	}
?>