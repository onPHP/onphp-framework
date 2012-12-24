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

	final class StandardComparator extends Singleton
		implements Comparator, Instantiatable
	{
 		private $cmpFunction = 'strcmp';
		
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		public function setCmpFunction($name)
		{
			$this->cmpFunction = $name;
			
			return $this;
		}
		
		public function compare($one, $two)
		{
			$cmpFunc = $this->cmpFunction;
			
			return $cmpFunc($one, $two);
		}
	}

