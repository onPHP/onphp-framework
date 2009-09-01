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

	abstract class StaticStorage
	{
		protected $baseUrl = null;
		
		abstract public function getUrl($name);
		
		//TODO: Use main/Application/ApplicationUrl
		public function __construct(AppUrl $baseUrl)
		{
			$this->baseUrl = $baseUrl;
		}
	}
?>