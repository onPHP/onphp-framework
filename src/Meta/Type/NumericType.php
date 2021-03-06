<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Meta\Type;

use OnPHP\Core\OSQL\DataType;

/**
 * @ingroup Types
**/
final class NumericType extends FloatType
{
	public function toColumnType()
	{
		return DataType::class.'::create('.DataType::class.'::NUMERIC)';
	}
}
?>