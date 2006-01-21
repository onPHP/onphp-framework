<?php
/***************************************************************************
 *   Copyright (C) 2005 by Anton E. Lebedevich                             *
 *   noiselist@pochta.ru                                                   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Container for passing values into OSQL queries.
	 * 
	 * @ingroup OSQL
	**/
	class DBValue extends Castable implements DialectString
	{
		private $value = null;
		
		public static function create($value)
		{
			return new DBValue($value);
		}

		public function __construct($value)
		{
			$this->value = $value;
		}

		public function getValue()
		{
			return $this->value;
		}

		public function toString(Dialect $dialect)
		{
			$out = $dialect->quoteValue($this->value);
			
			return
				$this->cast
					? $dialect->toCasted($out, $this->cast)
					: $out;
		}
	}
?>