<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Builders
	**/
	final class AutoClassBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();
			
			$out .= "abstract class Auto{$class->getName()}";
			
			$isNamed = false;
			
			if ($parent = $class->getParent())
				$out .= " extends {$parent->getName()}";
			elseif (
				$class->getPattern() instanceof DictionaryClassPattern
				&& $class->hasProperty('name')
			) {
				$out .= " extends NamedObject";
				$isNamed = true;
			} elseif (!$class->getPattern() instanceof ValueObjectPattern)
				$out .= " extends IdentifiableObject";
			
			if ($interfaces = $class->getInterfaces())
				$out .= ' implements '.implode(', ', $interfaces);
			
			$out .= "\n{\n";
			
			foreach ($class->getProperties() as $property) {
				if (!self::doPropertyBuild($class, $property, $isNamed))
					continue;
				
				$out .=
					"protected \${$property->getName()} = "
					."{$property->getType()->getDeclaration()};\n";
				
				if ($property->getFetchStrategyId() == FetchStrategy::LAZY) {
					$out .=
						"protected \${$property->getName()}Id = null;\n";
				}
			}
			
			$valueObjects = array();
			
			foreach ($class->getProperties() as $property) {
				if (
					$property->getType() instanceof ObjectType
					&& !$property->getType()->isGeneric()
					&& $property->getType()->getClass()->getPattern()
						instanceof ValueObjectPattern
				) {
					$valueObjects[$property->getName()] =
						$property->getType()->getClassName();
				}
			}
			
			if ($valueObjects) {
				$out .= <<<EOT

public function __construct()
{

EOT;
				foreach ($valueObjects as $propertyName => $className) {
					$out .= "\$this->{$propertyName} = new {$className}();\n";
				}
				
				$out .= "}\n";
			}
			
			foreach ($class->getProperties() as $property) {
				if (!self::doPropertyBuild($property, $isNamed))
					continue;
				
				$out .= $property->toMethods($class);
			}
			
			$out .= "}\n";
			$out .= self::getHeel();
			
			return $out;
		}
		
		private static function doPropertyBuild(
			MetaClass $class,
			MetaClassProperty $property,
			$isNamed
		)
		{
			if ($class->isRedefinedProperty($property->getName()))
				return false;
			
			if ($isNamed && $property->getName() == 'name')
				return false;
			
			if (
				($property->getName() == 'id')
				&& !$property->getClass()->getParent()
			)
				return false;
			
			// do not redefine parent's properties
			if (
				$property->getClass()->getParent()
				&& array_key_exists(
					$property->getName(),
					$property->getClass()->getAllParentsProperties()
				)
			)
				return false;
			
			return true;
		}
	}
?>