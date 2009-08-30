<?php
/***************************************************************************
 *   Copyright (C) 2009 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	final class TSearchVectorConcatenator implements DialectString
	{
		const CONCAT = ' || ';
		
		protected $chunks = array();
		
		public function concat(DialectString $string)
		{
			$this->chunks[] = $string;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			$strings = array();
			
			foreach ($this->chunks as $chunk)
				$strings[] = $chunk->toDialectString($dialect);
			
			return implode(self::CONCAT, $strings);
		}
	}
?>