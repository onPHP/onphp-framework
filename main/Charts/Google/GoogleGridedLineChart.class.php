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

	final class GoogleGridedLineChart extends GoogleNormalizedLineChart
	{
		private $grid = null;
		
		/**
		 * @return \Onphp\GoogleGridedLineChart
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return \Onphp\GoogleGridedLineChart
		**/
		public function setGrid(GoogleChartGrid $grid)
		{
			$this->grid = $grid;
			
			return $this;
		}
		
		/**
		 * @return \Onphp\GoogleChartGrid
		**/
		public function getGrid()
		{
			return $this->grid;
		}
		
		public function toString()
		{
			if (!$this->grid)
				$this->createDefault();
			
			$string = parent::toString();
			
			$string .= '&'.$this->grid->toString();
			
			return $string;
		}
		
		/**
		 * @return \Onphp\GoogleGridedLineChart
		**/
		private function createDefault()
		{
			$this->grid = GoogleChartGrid::create();
			
			$maxSteps = $this->getData()->getMaxSteps();
			
			if ($maxSteps > 0)
				$this->grid->setVerticalStepSize(round(100 / $maxSteps, 1));
			
			if (
				(
					$axis = $this->axesCollection->getAxisByTypeId(
						GoogleChartAxisType::X
					)
				) && ($label = $axis->getLabel())
				&& ($label->getCount() > 1)
			)
				$this->grid->setHorizontalStepSize(
					round(100 / ($label->getCount() - 1), 2)
				);
			
			return $this;
		}
	}
?>