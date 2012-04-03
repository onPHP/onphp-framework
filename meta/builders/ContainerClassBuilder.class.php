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

			$containerName = $class->getName().ucfirst($holder->getName()).'DAO';

			$isNoSQL = is_subclass_of($holder->getType()->getClassName(), 'NoSqlObject');
			$parentExtend = $isNoSQL ? 'NoSql' : '';

			$out .=
				'final class '
				.$containerName
				.' extends '
				.$holder->getRelation()->toString().$parentExtend.'Linked'
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
				$helper_table_name = $class->getTableName().'_'.$remoteColumnName;
				if( strcmp($class->getTableName(), $remoteColumnName)>=0 ) {
					$helper_table_name = $remoteColumnName.'_'.$class->getTableName();
				}
					$out .= <<<EOT

public function getHelperTable()
{
	return '{$helper_table_name}';
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