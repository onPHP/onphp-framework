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
	
	class EdgeView extends QueryObject
	{
		private $cubeView		= null;
		
		private $dimensionViews	= null;
		
		// unimplemented:
		private $edgeFilters	= array();
		private $segments		= array();	// 1 or more?
		
		public function __construct(CubeView $cubeView)
		{
			$this->cubeView = $cubeView;
		}
		
		public function addDimensionView(DimensionView $dimensionView)
		{
			$this->dimensionViews[] = $dimensionView;
			
			return $this;
		}
	}
?>