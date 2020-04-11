<?php
/***************************************************************************
 *   Copyright (C) 2008 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Meta\Type;

use OnPHP\Core\OSQL\DataType;
use OnPHP\Main\Net\HttpUrl;

/**
 * @ingroup Types
**/
final class HttpUrlType extends ObjectType
{
	public function getPrimitiveName()
	{
		return 'httpUrl';
	}
	
	public function isGeneric()
	{
		return true;
	}
	
	public function getFullClass() {
		return HttpUrl::class;
	}
	
	public function isMeasurable()
	{
		return true;
	}
	
	public function toColumnType()
	{
		return DataType::class.'::create('.DataType::class.'::VARCHAR)';
	}
}
?>