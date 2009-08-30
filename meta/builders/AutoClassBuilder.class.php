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
			
			if ($valueObjects = $class->getValueObjectList()) {
				$out .= <<<EOT

public function __construct()
{

EOT;
				if (
					$class->getParent()
					&& $class->hierarchyHaveValueObjects()
				) {
					$out .= "parent::__construct();\n\n";
				}
				
				foreach ($valueObjects as $property) {
					$out .=
						"\$this->{$property->getName()} "
						."= new {$property->getType()->getClassName()}();\n";
				}
				
				$out .= "}\n";
			}
			
			if ($encapsulants = $class->getEncapsulantList()) {
				$out .= <<<EOT

public function __clone()
{

EOT;
				
				if (
					$class->getParent()
					&& $class->hierarchyHaveEncapsulants()
				) {
					$out .= "parent::__clone();\n\n";
				}
				
				foreach ($encapsulants as $property) {
					$out .= <<<EOT
if (\$this->{$property->getName()})
	\$this->{$property->getName()} = clone \$this->{$property->getName()};


EOT;
				}
				
				$out = rtrim($out)."\n}\n";
			}
			
			if ($containers = $class->getContainersList()) {
				$propertyList = $class->getProperties();
				
				foreach ($containers as $property) {
					unset($propertyList[$property->getName()]);
				}
				
				if ($propertyList) {
					$out .= <<<EOT

public function __sleep()
{

EOT;
					
					if (
						$class->getParent()
						&& $class->hierarchyHaveContainers()
					) {
						$out .= "parent::__sleep();\n\n";
					}
					
					$out .= 'return array(';
					
					foreach ($propertyList as $property) {
						$out .= "'{$property->getName()}', ";
					}
					
					$out = rtrim($out, ', ').");\n}\n";
				}
			}
			
			foreach ($class->getProperties() as $property) {
				if (!self::doPropertyBuild($class, $property, $isNamed))
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
			if (
				$parentProperty =
					$class->isRedefinedProperty($property->getName())
			) {
				// check wheter property fetch strategy becomes lazy
				if (
					(
						$parentProperty->getFetchStrategyId()
						<> $property->getFetchStrategyId()
					) && (
						$property->getFetchStrategyId() === FetchStrategy::LAZY
					)
				)
					return true;
				
				return false;
			}
			
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