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
/* $Id$ */

	/**
	 * @ingroup Builders
	**/
	final class AutoDaoBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			if (
				(!$parent = $class->getParent())
				|| (
					$class->getFinalParent()->getPattern()
						instanceof InternalClassPattern
				)
			)
				return DictionaryDaoBuilder::build($class);
			
			if (
				$class->getFinalParent()->getPattern()
					instanceof InternalClassPattern
			) {
				$parentName = 'StorableDAO';
			} else {
				$parentName = $parent->getName().'DAO';
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
?>