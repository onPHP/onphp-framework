<?php
/***************************************************************************
 *   Copyright (C) 2012 by Timofey A. Anisimov                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Core\OSQL;

use OnPHP\Core\DB\Dialect;

/**
 * @ingroup OSQL
**/
final class SQLFullOuterJoin extends SQLBaseJoin
{
	/**
	 * @param Dialect $dialect
	 * @return string
	 */
	public function toDialectString(Dialect $dialect)
	{
		return parent::baseToString($dialect, 'FULL OUTER ');
	}

}
