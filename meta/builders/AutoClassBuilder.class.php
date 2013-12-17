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
	namespace Onphp;

	final class AutoClassBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();
			
			if ($namespace = trim($class->getNamespace(), '\\'))
				$out .= "namespace {$namespace};\n\n";
				
			$out .= "abstract class {$class->getName('Auto')}";
			
			$isNamed = false;
			
			if ($parent = $class->getParent())
				$out .= " extends {$parent->getFullClassName()}";
			elseif (
				$class->getPattern() instanceof DictionaryClassPattern
				&& $class->hasProperty('name')
			) {
				$out .= " extends \Onphp\NamedObject";
				$isNamed = true;
			} elseif (!$class->getPattern() instanceof ValueObjectPattern)
				$out .= " extends \Onphp\IdentifiableObject";
			
			if ($interfaces = $class->getInterfaces())
				$out .= ' implements '.implode(', ', $interfaces);
			
			$out .= "\n{\n";
			
			foreach ($class->getProperties() as $property) {
				/* @var $property \Onphp\MetaClassProperty */
				if (!self::doPropertyBuild($class, $property, $isNamed))
					continue;
				
				if ($property->getFetchStrategyId() == FetchStrategy::LAZY) {
					$out .=
						"protected \${$property->getName()}Id = null;\n";
				} else {
					$out .=
						"protected \${$property->getName()} = "
						."{$property->getType()->getDeclaration()};\n";
				}
			}
			
			$valueObjects = array();
			
			foreach ($class->getProperties() as $property) {
				/* @var $property \Onphp\MetaClassProperty */
				if (
					$property->getType() instanceof ObjectType
					&& !$property->getType()->isGeneric()
					&& $property->getType()->getClass()->getPattern()
						instanceof ValueObjectPattern
				) {
					$valueObjects[$property->getName()] =
						$property->getType()->getClass()->getFullClassName();
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

			$out .= self::staticCallsBuild($class);

			foreach ($class->getProperties() as $property) {
				/* @var $property \Onphp\MetaClassProperty */
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

		private static function staticCallsBuild(
			MetaClass $class
		)
		{
			$out = '';
			if (
				$class->getPattern()->daoExists()
				&& (!$class->getPattern() instanceof AbstractClassPattern)
			) {
				$daoName = $class->getFullClassName('', 'DAO');
				$dao = <<<EOT
	/**
	 * @return {$daoName}
	**/
	public static function dao()
	{
		return \Onphp\Singleton::getInstance('{$daoName}');
	}

EOT;
			} else {
				$dao = null;
			}


			if ($type = $class->getType())
				$typeName = $type->toString().' ';
			else
				$typeName = null;

			if (!$type || $type->getId() !== MetaClassType::CLASS_ABSTRACT) {
				$customCreate = null;

				if (
					$class->getFinalParent()->getPattern()
						instanceof InternalClassPattern
				) {
					$parent = $class;

					while ($parent = $parent->getParent()) {
						/* @var $parent MetaClass */
						$info = new \ReflectionClass($parent->getFullClassName());

						if (
							$info->hasMethod('create')
							&& ($info->getMethod('create')->getParameters() > 0)
						) {
							$customCreate = true;
							break;
						}
					}
				}

				if ($customCreate) {
					$creator = $info->getMethod('create');

					$declaration = array();

					foreach ($creator->getParameters() as $parameter) {
						$declaration[] =
							'$'.$parameter->getName()
							// no one can live without default value @ ::create
							.' = '
							.(
								$parameter->getDefaultValue()
									? $parameter->getDefaultValue()
									: 'null'
							);
					}

					$declaration = implode(', ', $declaration);

					$out .= <<<EOT

	/**
	 * @return {$class->getFullClassName()}
	**/
	public static function create({$declaration})
	{
		return new static({$declaration});
	}

EOT;
				} else {
					$out .= <<<EOT

	/**
	 * @return {$class->getFullClassName()}
	**/
	public static function create()
	{
		return new static;
	}

EOT;
				}

				$protoName = $class->getFullClassName('Proto');

				$out .= <<<EOT

{$dao}
	/**
	 * @return {$protoName}
	**/
	public static function proto()
	{
		return \Onphp\Singleton::getInstance('{$protoName}');
	}

EOT;

			}

			return $out;
		}
	}
?>