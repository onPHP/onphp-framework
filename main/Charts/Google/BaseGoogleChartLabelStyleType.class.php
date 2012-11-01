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

	// TODO: support for other types
	
	/**
	 * @ingroup GoogleChart
	**/
	namespace Onphp;

	abstract class BaseGoogleChartLabelStyleType
	{
		protected $name = null;
		
		public function toString()
		{
			return $this->name;
		}
	}
?>