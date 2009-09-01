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

	class Cube
	{
		private $dimensions = array();
		
		/**
		 * @return Cube
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return Cube
		**/
		public function addDimension(Dimension $dimension)
		{
			$this->dimensions[] = $dimension;
			
			return $this;
		}
		
		public function getCursor(QueryObject $view)
		{
			throw new UnimplementedFeatureException('implement me');
		}
	}
?>