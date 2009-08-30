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
	
	abstract class LocationSettings
	{
		// redefine me
		private $locations = array();
		
		/**
		 * @return LocationSettings
		**/
		//TODO: Use main/Application/ApplicationUrl
		public function set($area, AppUrl $location)
		{
			$this->locations[$area] = $location;
			
			return $this;
		}
		
		public function get($area)
		{
			if (!isset($this->locations[$area]))
				throw new WrongArgumentException(
					"location {{$area}} does not defined"
				);
			
			return $this->locations[$area];
		}
	}
?>