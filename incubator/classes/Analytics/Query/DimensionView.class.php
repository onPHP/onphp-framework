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
	
	class DimensionView extends QueryObject
	{
		private $dimension				= null;
		private $dimensionStepManager	= null;
		
		public function __construct(Dimension $dimension)
		{
			$this->dimension = $dimension;
			$this->dimensionStepManager = new DimensionStepManager($this);
		}
		
		/**
		 * @return DimensionView
		**/
		public static function create(Dimension $dimension)
		{
			return new self($dimension);
		}
		
		/**
		 * @return Dimension
		**/
		public function getDimension()
		{
			return $this->dimension;
		}
		
		/**
		 * @return DimensionStepManager
		**/
		// TODO: may be createDimensionStepManager()?
		public function getDimensionStepManager()
		{
			return $this->dimensionStepManager;
		}
	}
?>