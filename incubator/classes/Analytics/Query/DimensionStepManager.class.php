<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
	
	class DimensionStepManager
	{
		private $dimensionView	= null;
		
		private $steps			= array();
		
		public function __construct(DimensionView $dimensionView)
		{
			$this->dimensionView = $dimensionView;
		}
		
		/**
		 * @return DimensionView
		**/
		public function getDimensionView()
		{
			return $this->dimensionView;
		}
		
		/**
		 * @return DimensionStepManager
		**/
		public function addStep(DimensionStep $step)
		{
			Assert::isTrue($step->getManager() === $this);
			
			$this->steps[] = $step;
			
			return $this;
		}
		
		/**
		 * @return DimensionStep
		**/
		public function createStep($type)
		{
			$result = DimensionStep::createByType($this, $type);
			
			$this->steps[] = $result;
			
			return $result;
		}
	}
?>