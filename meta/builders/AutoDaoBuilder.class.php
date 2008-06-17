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
				$parentName = 'ComplexBuilderDAO';
			} else {
				$parentName = $parent->getName().'DAO';
			}
			
			$className = $class->getName();
			$varName = strtolower($className[0]).substr($className, 1);

			$out = self::getHead();
			
			$out .= <<<EOT
abstract class Auto{$class->getName()}DAO extends {$parentName}
{

EOT;

			if (sizeof($class->getWithInternalProperties())) {
				$out .= self::buildPointers($class);
				
				if (
					($parent = $class->getParent())
					&& !($parent->getPattern() instanceof AbstractClassPattern)
				) {
					$typeHint = $parent->getFinalParent()->getName();
				} else {
					$typeHint = '/* '.$class->getName().' */';
				}

				$out .= <<<EOT


/**
 * @return InsertOrUpdateQuery
**/
public function setQueryFields(InsertOrUpdateQuery \$query, {$typeHint} \${$varName})
{
	parent::setQueryFields(\$query, \${$varName});


EOT;
				$out .= self::buildFillers($class);
			} else {
				$out .= self::buildPointers($class);
				$out .= self::buildFillers($class);
			}
			
			return $out.self::getHeel();
		}
	}
?>