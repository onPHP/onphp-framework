<?php
/***************************************************************************
 *   Copyright (C) 2011 by Evgeny V. Kokovikhin                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Meta\Type;

use OnPHP\Core\OSQL\DataType;
use OnPHP\Main\Net\Ip\IpRange;

/**
 * @ingroup Types
**/
class IpRangeType extends ObjectType
{
	public function getPrimitiveName()
	{
		return 'ipRange';
	}
	
	public function isGeneric()
	{
		return true;
	}
	
	public function getFullClass() {
		return IpRange::class;
	}
	
	public function isMeasurable()
	{
		return true;
	}
	
	public function toColumnType()
	{
		return DataType::class.'::create('.DataType::class.'::IP_RANGE)';
	}
}
?>