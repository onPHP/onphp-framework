<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
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
			
			$out .=
				'final class '
				.$class->getName().ucfirst($holder->getName()).'DAO'
				.' extends '
				.$holder->getRelation()->toString().'Linked'
				."\n{\n";

			$className = $class->getName();
			$propertyName = strtolower($className[0]).substr($className, 1);
			
			$remoteDumbName =
				MetaConfiguration::me()->getClassByName(
					$holder->getType()->getClass()
				)->
				getDumbName();
			
			$out .= <<<EOT
public function __construct({$className} \${$propertyName}, \$lazy = false)
{
	parent::__construct(
		\${$propertyName},
		{$holder->getType()->getClass()}::dao(),
		\$lazy
	);
}

public static function create({$className} \${$propertyName}, \$lazy = false)
{
	return new self(\${$propertyName}, \$lazy);
}

EOT;

			if ($holder->getRelation()->getId() == MetaRelation::MANY_TO_MANY) {
				$out .= <<<EOT

public function getHelperTable()
{
	return '{$class->getDumbName()}_{$remoteDumbName}';
}

EOT;
			}
			
			$out .= <<<EOT

public function getChildIdField()
{
	return '{$remoteDumbName}_id';
}

public function getParentIdField()
{
	return '{$class->getDumbName()}_id';
}

EOT;
			
			
			$out .= "}\n";
			$out .= self::getHeel();
			
			return $out;
		}
	}
?>