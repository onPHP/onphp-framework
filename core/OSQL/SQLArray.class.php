<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Values row implementation.
	 * 
	 * @ingroup OSQL
	**/
	class SQLArray implements DialectString
	{
		private $array = array();
		
		public function __construct($array)
		{
			$this->array = $array;
		}
		
		public function getArray()
		{
			return $this->array;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			$array = $this->array;

			if (is_array($array)) {
				$quoted = array();
				
				foreach ($array as $item)
					$quoted[] = $dialect->quoteValue($item);

				$value = implode(', ', $quoted);
			} else
				$value = $dialect->quoteValue($array);
			
			return "({$value})";
		}
	}
?>