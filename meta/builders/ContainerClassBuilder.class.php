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

			if ($namespace = trim($class->getNamespace(), '\\'))
				$out .= "namespace {$namespace};\n\n";
			
			$containerName = $class->getName('',ucfirst($holder->getName()).'DAO');
			$containerFullName = $class->getFullClassName('',ucfirst($holder->getName()).'DAO');
			
			$out .=
				'final class '
				.$containerName
				.' extends '
				.'\\'.$holder->getRelation()->toString().'Linked'
				."\n{\n";

			$className = $class->getName();
			$propertyName = strtolower($className[0]).substr($className, 1);
			
			$remoteColumnName = $holder->getType()->getClass()->getTableName();
			
			$out .= <<<EOT
public function __construct({$class->getFullClassName()} \${$propertyName}, \$lazy = false)
{
	parent::__construct(
		\${$propertyName},
		{$holder->getType()->getFullClassName()}::dao(),
		\$lazy
	);
}

/**
 * @return {$containerFullName}
**/
public static function create({$class->getFullClassName()} \${$propertyName}, \$lazy = false)
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