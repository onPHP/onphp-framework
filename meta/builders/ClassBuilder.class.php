<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class ClassBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();
			
			$out .= "\tabstract class {$class->getName()}";
			
			if ($interfaces = $class->getInterfaces())
				$out .= ' implements '.implode(', ', $interfaces);
			
			$out .= "\n\t{\n";
			
			foreach ($class->getProperties() as $property) {
				$out .=
					"\t\tprotected {$property->getName()} = "
					."{$property->getType()->getDeclaration()};\n";
			}
			
			$out .= "\n";
			
			foreach ($class->getProperties() as $property) {
				$out .= MethodUtils::getter($property);
				$out .= MethodUtils::setter($property);
				
				if ($property->getType() instanceof ObjectType)
					$out .= MethodUtils::dropper($property);
			}
			
			$out .= "\t}\n";
			$out .= self::getHeel();
			
			echo $out."\n\n";
			
			die();
		}
	}
?>