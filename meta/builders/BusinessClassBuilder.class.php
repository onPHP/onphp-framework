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

	final class BusinessClassBuilder extends OnceBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();

			if ($namespace = trim($class->getNamespace(), '\\'))
				$out .= "namespace {$namespace};\n\n";

			if ($type = $class->getType())
				$typeName = $type->toString().' ';
			else
				$typeName = null;

			$interfaces = ' implements \Onphp\Prototyped';

			if (
				$class->getPattern()->daoExists()
				&& (!$class->getPattern() instanceof AbstractClassPattern)
			) {
				$interfaces .= ', \Onphp\DAOConnected';
			}

			$out .= <<<EOT
{$typeName}class {$class->getName()} extends {$class->getFullClassName('Auto')}{$interfaces}
{
EOT;


			$out .= <<<EOT

	// your brilliant stuff goes here
}

EOT;
			return $out.self::getHeel();
		}
	}
?>