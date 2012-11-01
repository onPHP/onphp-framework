<?php
/***************************************************************************
 *   Copyright (C) 2008 by Denis M. Gabaidulin                             *
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

	abstract class BaseGoogleChartParameter implements Stringable
	{
		protected $name = null;
		
		/**
		 * @return \Onphp\BaseGoogleChartParameter
		**/
		public function setName($name)
		{
			$this->name = $name;
			
			return $this;
		}
		
		public function getName()
		{
			return $this->name;
		}
	}
?>