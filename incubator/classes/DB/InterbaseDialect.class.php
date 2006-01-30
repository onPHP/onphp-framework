<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Interbase dialect.
	 *
	 * @see http://firebird.sourceforge.net/
	 * 
	 * @ingroup DB
	**/
	final class InterbaseDialect extends Dialect
	{
		public static function quoteField(&$field)
		{
			if (strpos($field, '.') !== false)
				return '"' . implode('".', explode('.', $field, 2));
			else
				return $field;
		}
		
		/// TODO: consider trigger generation too.
		public static function autoincrementize(DBColumn $column, &$prepend)
		{
			Assert::isTrue(
				($table = $column->getTable()) !== null
			);
			
			$sequenceName = $table->getName().'_id';
			
			$prepend = 'CREATE GENERATOR '.$sequenceName.';';
			
			return null;
		}
	}
?>