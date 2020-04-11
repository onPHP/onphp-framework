<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
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
final class SpookedEnumerationPattern extends EnumerationClassPattern
{
	/**
	 * @return SpookedEnumerationPattern
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