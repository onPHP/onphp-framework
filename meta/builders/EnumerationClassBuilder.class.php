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

	/**
	 * @ingroup Builders
	**/
	namespace Onphp;

	final class EnumerationClassBuilder extends OnceBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();
			
			if ($type = $class->getType())
				$type = "{$type->getName()} ";
			else
				$type = null;
			
			if ($namespace = trim($class->getNamespace(), '\\'))
				$out .= "namespace {$namespace};\n\n";
			
			$out .= <<<EOT
{$type}class {$class->getName()} extends \Onphp\Enumeration
{
	// implement me!
}

EOT;
			
			return $out.self::getHeel();
		}
	}
?>