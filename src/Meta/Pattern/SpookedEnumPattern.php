<?php
/***************************************************************************
 *   Copyright (C) 2012 by Georgiy T. Kutsurua                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Meta\Pattern;

use OnPHP\Meta\Entity\MetaClass;

/**
 * @ingroup Patterns
**/
final class SpookedEnumPattern extends EnumClassPattern
{
	/**
	 * @return SpookedEnumPattern
	**/
	public function build(MetaClass $class)
	{
		return $this;
	}

	public function daoExists()
	{
		return false;
	}
}
?>