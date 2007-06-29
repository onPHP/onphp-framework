<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich                        *
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
	 * @ingroup Module
	**/
	final class DBValue extends Castable implements DialectString, Aliased
	{
		private $value = null;
		private $alias = null;
		
		/**
		 * @return DBValue
		**/
		public static function create($value, $alias = null)
		{
			return new self($value, $alias);
		}

		public function __construct($value, $alias = null)
		{
			$this->value = $value;
			$this->alias = $alias;
		}

		public function getValue()
		{
			return $this->value;
		}
		
		public function getAlias()
		{
			return $this->alias;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			$out = $dialect->quoteValue($this->value);
			
			return
				$this->cast
					? $dialect->toCasted($out, $this->cast)
					: $out
				.(
					$this->alias
						? ' AS '.$dialect->quoteField($this->alias)
						: null
				);
		}
	}
?>