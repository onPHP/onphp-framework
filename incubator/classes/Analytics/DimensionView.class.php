<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */
	
	class DimensionView extends QueryObject
	{
		private $dimension				= null;
		private $dimensionStepManager	= null;
		
		public function __construct(Dimension $dimension)
		{
			$this->dimension = $dimension;
			$this->dimensionStepManager = new DimensionStepManager($this);
		}
		
		public static function create(Dimension $dimension)
		{
			return new self($dimension);
		}
		
		public function getDimension()
		{
			return $this->dimension;
		}
		
		// TODO: may be createDimensionStepManager()?
		public function getDimensionStepManager()
		{
			return $this->dimensionStepManager;
		}
	}
?>