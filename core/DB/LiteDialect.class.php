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
	 * SQLite dialect.
	 * 
	 * @see http://www.sqlite.org/
	 * 
	 * @ingroup DB
	**/
	final class LiteDialect extends Dialect
	{
		public static function quoteValue(&$value)
		{
			/// @see Sequenceless for this convention
			
			if ($value instanceof Identifier && !$value->isFinalized())
				return 'null';
			
			return "'" .sqlite_escape_string($value)."'";
		}
		
		public static function dropTableMode($cascade = false)
		{
			return null;
		}
		
		public static function autoincrementize(DBColumn $column, &$prepend)
		{
			$type = $column->getType();
			
			Assert::isTrue(
				(
					$type->getId() == DataType::BIGINT
					|| $type->getId() == DataType::INTEGER
				)
				&& $column->isPrimaryKey()
			);
			
			return null; // or even 'AUTOINCREMENT'?
		}
	}
?>