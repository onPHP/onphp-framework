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

namespace OnPHP\Meta\Builder;

use OnPHP\Main\DAO\StorableDAO;
use OnPHP\Meta\Entity\MetaClass;

/**
 * @ingroup Builders
**/
final class DictionaryDaoBuilder extends BaseBuilder
{
	public static function build(MetaClass $class)
	{
		$out = self::getHead();
		
		$uses = [StorableDAO::class];
		
		$out .= "\nnamespace {$class->getAutoDaoNamespace()};\n\n";
		
		foreach($uses as $use) {
			$out .= "use $use;\n";
		}
		
		$out .= <<<EOT

abstract class Auto{$class->getName()}DAO extends StorableDAO
{

EOT;

		$pointers = self::buildPointers($class);
		
		$out .= <<<EOT
{$pointers}
}

EOT;
		
		return $out.self::getHeel();
	}
}
?>