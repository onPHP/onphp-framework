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

use OnPHP\Main\DAO\StorableDAO;
use OnPHP\Meta\Entity\MetaClass;
use OnPHP\Meta\Pattern\InternalClassPattern;

/**
 * @ingroup Builders
**/
final class AutoDaoBuilder extends BaseBuilder
{
	public static function build(MetaClass $class)
	{
		if (!$class->hasBuildableParent()) {
			return DictionaryDaoBuilder::build($class);
		} else {
			$parent = $class->getParent();
		}

		if (
			$class->getParent()->getPattern()
				instanceof InternalClassPattern
		) {
			$parentName = StorableDAO::class;
			$uses = StorableDAO::class;
		} else {
			$parentName = $parent->getName().'DAO';
			$uses = "{$class->getDaoNamespace()}\\{$parentName}";
		}

		$out = self::getHead();
		
		$out .= <<<EOT
namespace {$class->getAutoDaoNamespace()};

use $uses;

		
EOT;

		$out .= <<<EOT
abstract class Auto{$class->getName()}DAO extends {$parentName}
{

EOT;

		$out .= self::buildPointers($class)."\n}\n";

		return $out.self::getHeel();
	}
}
?>