<?php
/***************************************************************************
 *	 Created by Alexey V. Gorbylev at 29.12.2011                           *
 *	 email: alex@gorbylev.ru, icq: 1079586, skype: avid40k                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Builders
 */
final class AutoNoSqlDaoBuilder extends BaseBuilder {

	public static function build(MetaClass $class)
	{
		if (
			is_null( $class->getParent() )
			||
			$class->getParent()->getPattern() instanceof InternalClassPattern
		) {
			$parentName = 'NoSqlDAO';
		} else {
			$parentName = $class->getParent()->getName().'DAO';
		}

		$out = self::getHead();

		$out .= <<<EOT
abstract class Auto{$class->getName()}DAO extends {$parentName}
{

EOT;

		$out .= self::buildPointers($class)."\n}\n";

		return $out.self::getHeel();
	}

}
