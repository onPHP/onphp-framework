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
use OnPHP\Meta\Entity\MetaConfiguration;
use OnPHP\Meta\Console\Format;
use OnPHP\Meta\Builder\EnumClassBuilder;

/**
 * @ingroup Patterns
**/
class EnumClassPattern extends BasePattern
{
	public function daoExists()
	{
		return false;
	}

	public function tableExists()
	{
		return false;
	}

	/**
	 * @return EnumClassPattern
	**/
	public function build(MetaClass $class)
	{
		$userFile = ONPHP_META_BUSINESS_DIR.$class->getName().EXT_CLASS;

		if (
			MetaConfiguration::me()->isForcedGeneration()
			|| !file_exists($userFile)
		)
			$this->dumpFile(
				$userFile,
				Format::indentize(EnumClassBuilder::build($class))
			);

		return $this;
	}
}
?>