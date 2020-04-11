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

namespace OnPHP\Meta\Builder;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Base\IdentifiableObject;
use OnPHP\Main\Criteria\FetchStrategy;
use OnPHP\Meta\Entity\MetaClass;
use OnPHP\Meta\Entity\MetaClassNameBuilder;
use OnPHP\Meta\Entity\MetaClassProperty;
use OnPHP\Meta\Entity\MetaRelation;
use OnPHP\Meta\Pattern\DictionaryClassPattern;
use OnPHP\Meta\Pattern\ValueObjectPattern;
use OnPHP\Meta\Type\BooleanType;
use OnPHP\Meta\Type\ObjectType;
use OnPHP\Meta\Util\MetaClassPull;

/**
 * @ingroup Builders
**/
final class AutoClassBuilder extends BaseBuilder
{
	public static function build(MetaClass $class)
	{
		$out = self::getHead();
		
		$out .= "namespace {$class->getAutoNamespace()};"
			. "\n\n";

		$uses = array(
			IdentifiableObject::class, 
			MetaClassNameBuilder::getClassOfMetaClass($class)
		);
		
		foreach ($class->getProperties() as $property) {
			$dependency = null;
			
			if ($property->getType() instanceof ObjectType) {
				if (
					$property->getRelationId() == MetaRelation::ONE_TO_MANY
					|| $property->getRelationId() == MetaRelation::MANY_TO_MANY
				) {
					$dependency = MetaClassNameBuilder::getContainerClassOfMetaProperty($property);
				} else {
					$dependency = MetaClassNameBuilder::getClassOfMetaProperty($property);
				}
			} elseif (
				$property->getType() instanceof BooleanType 
				&& $property->isOptional()
			) {
				$dependency = Assert::class;
			}
			
			if (!is_null($dependency) && !in_array($dependency, $uses)) {
				$uses[] = $dependency;
			}
		}

		foreach($uses as $import) {
			$out .= "use $import;"
				. "\n";
		}
		
		$out .= "\n";
		
		$out .= "abstract class Auto{$class->getName()}";
		
		$isNamed = false;

		if ($parent = $class->getParent()) {
			$out .= " extends {$parent->getBusinessClass(true)}";
		} elseif (
			$class->getPattern() instanceof DictionaryClassPattern
			&& $class->hasProperty('name')
		) {
			$extendableClass = MetaClassPull::me()->getClass('NamedObject')->getNameWithNs(true);
			$out .= " extends {$extendableClass}";
			$isNamed = true;
		} elseif (!$class->getPattern() instanceof ValueObjectPattern) {
			$extendableClass = MetaClassPull::me()->getClass('IdentifiableObject')->getNameWithNs(true);
			$out .= " extends {$extendableClass}";
		}

		if ($interfaces = $class->getInterfaces()) {
			$out .= ' implements '.implode(', ', $interfaces);
		}

		$out .= "\n{\n";

		foreach ($class->getProperties() as $property) {
			if (!self::doPropertyBuild($class, $property, $isNamed)) {
				continue;
			}

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
				$parentProperty->getFetchStrategyId() <> $property->getFetchStrategyId()
				&& $property->getFetchStrategyId() === FetchStrategy::LAZY
			) {
				return true;
			}

			return false;
		}

		if ($isNamed && $property->getName() == 'name') {
			return false;
		}

		if ($property->getName() == 'id'
			&& !$property->getClass()->getParent()
		) {
			return false;
		}

		// do not redefine parent's properties
		if (
			$property->getClass()->getParent()
			&& array_key_exists(
				$property->getName(),
				$property->getClass()->getAllParentsProperties()
			)
		) {
			return false;
		}

		return true;
	}
}
?>