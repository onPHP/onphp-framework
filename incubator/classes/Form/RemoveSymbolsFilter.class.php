<?php
/***************************************************************************
 *   Copyright (C) 2005 by Sveta Smirnova                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class RemoveSymbolsFilter extends BaseFilter
	{
		private $symbols = array();
		
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
					array_fill(0, count($this->symbols), ''),
					$value
				);
		}
	}
?>