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
use OnPHP\Core\Exception\UnsupportedMethodException;
use OnPHP\Meta\Entity\MetaClassProperty;
use OnPHP\Meta\Entity\MetaRelation;
use OnPHP\Meta\Entity\MetaClassNameBuilder;

	/**
	 * @ingroup Builders
	**/
	final class ContainerClassBuilder extends OnceBuilder
	{
		public static function build(MetaClass $class)
		{
			throw new UnsupportedMethodException();
		}
		
		public static function buildContainer(
			MetaClass $class, MetaClassProperty $holder
		)
		{
			$out = self::getHead();
			
			$out .= "\nnamespace {$class->getDaoNamespace()};\n\n";
			
			$containerName = $class->getName() . ucfirst($holder->getName()) . 'DAO';
			$containerType = $holder->getRelation()->toString() . 'Linked';
			
			$uses = [
					'OnPHP\Main\UnifiedContainer\\'.$containerType,
					MetaClassNameBuilder::getClassOfMetaClass($class),
					MetaClassNameBuilder::getClassOfMetaProperty($holder),
			];
			
			foreach ($uses as $use) {
				$out .= "use $use;\n";
			}
			
			$out .= "\n";
			
			$out .=
				'final class '
				.$containerName
				.' extends '
				.$holder->getRelation()->toString().'Linked'
				."\n{\n";

			$className = $class->getName();
			$propertyName = strtolower($className[0]).substr($className, 1);
			
			$remoteColumnName = $holder->getType()->getClass()->getTableName();
			
			$out .= <<<EOT
public function __construct({$className} \${$propertyName}, \$lazy = false)
{
	parent::__construct(
		\${$propertyName},
		{$holder->getType()->getClassName()}::dao(),
		\$lazy
	);
}

/**
 * @return {$containerName}
**/
public static function create({$className} \${$propertyName}, \$lazy = false)
{
	return new self(\${$propertyName}, \$lazy);
}

EOT;

			if ($holder->getRelation()->getId() == MetaRelation::MANY_TO_MANY) {
				$out .= <<<EOT

public function getHelperTable()
{
	return '{$class->getTableName()}_{$remoteColumnName}';
}

public function getChildIdField()
{
	return '{$remoteColumnName}_id';
}

EOT;
			}
			
			$out .= <<<EOT

public function getParentIdField()
{
	return '{$class->getTableName()}_id';
}

EOT;
			
			
			$out .= "}\n";
			$out .= self::getHeel();
			
			return $out;
		}
	}
?>