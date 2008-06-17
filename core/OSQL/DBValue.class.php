<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Container for passing values into OSQL queries.
	 * 
	 * @ingroup OSQL
	 * @ingroup Module
	**/
	final class DBValue extends Castable implements DialectString
	{
		private $value = null;
		
		/**
		 * @return DBValue
		**/
		public static function create($value)
		{
			return new self($value);
		}
		
		public function __construct($value)
		{
			$this->value = $value;
		}
		
		public function getValue()
		{
			return $this->value;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			$out = $dialect->quoteValue($this->value);
			
			return
				$this->cast
					? $dialect->toCasted($out, $this->cast)
					: $out;
		}
	}
?>