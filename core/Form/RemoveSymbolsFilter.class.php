<?php
/***************************************************************************
 *   Copyright (C) 2007 by Sveta A. Smirnova                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	class RemoveSymbolsFilter implements Filtrator
	{
		private $symbols = array();
		
		public static function create()
		{
			return new RemoveSymbolsFilter();
		}
		
		public function setSymbols(/* ... */)
		{
			$this->symbols = func_get_args();
			
			return $this;
		}
		
		public function getSymbols()
		{
			return $this->symbols;
		}
		
		public function apply($value)
		{
			return
				str_replace(
					$this->symbols,
					null,
					$value
				);
		}
	}
?>