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
	
	class LevelBasedHierarchy extends Hierarchy
	{
		private $levels		= array();
		
		/**
		 * @return LevelBasedHierarchy
		**/
		// TODO: implement it correctly
		public function addLevel($level)
		{
			if (is_string($level)) {
				$this->levels[] = $level;
			} else if ($level instanceof SQLFunction) {
				$this->levels[] = $level;
			}
			
			return $this;
		}
	}
?>