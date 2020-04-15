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

use OnPHP\Meta\Entity\MetaClass;
use OnPHP\Core\Base\Enumeration;
use OnPHP\Meta\Util\NamespaceUtils;

/**
 * @ingroup Builders
**/
final class EnumerationClassBuilder extends OnceBuilder
{
	public static function build(MetaClass $class)
	{
		$out = self::getHead();
		
		$namespace = NamespaceUtils::getBusinessNS($class);
		
		$out .= "\nnamespace {$namespace};\n\n";
		
		$uses = Enumeration::class;
		
		$out .= "use {$uses};\n\n";
		
		if ($type = $class->getType())
			$type = "{$type->getName()} ";
		else
			$type = null;
		
		$out .= <<<EOT
{$type}class {$class->getName()} extends Enumeration
{
	// implement me!
}

EOT;
		
		return $out.self::getHeel();
	}
}
?>