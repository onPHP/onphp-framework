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
	final class ValueObjectDaoBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			$className = $class->getName();
			
			if (
				($parent = $class->getParent())
				&& !($parent->getPattern() instanceof AbstractClassPattern)
			) {
				$typeHint = $parent->getFinalParent()->getName();
			} else {
				$typeHint = '/* '.$className.' */';
			}
			
			$varName = strtolower($className[0]).substr($className, 1);
			
			$out = self::getHead();
			
			if ($class->hasBuildableParent()) {
				$parent = $class->getParent()->getName().'DAO';
			} else {
				$parent = 'ValueObjectDAO';
			}
			
			$out .= <<<EOT
abstract class Auto{$class->getName()}DAO extends {$parent}
{

EOT;
			if (sizeof($class->getWithInternalProperties())) {

				$out .= <<<EOT
	/**
	 * @return InsertOrUpdateQuery
	**/
	public function setQueryFields(InsertOrUpdateQuery \$query, {$typeHint} \${$varName})
	{

EOT;
			
				$out .= self::buildFillers($class);
			} else {
				$out .= <<<EOT
				
}

EOT;
			}
			
			return $out.self::getHeel();
		}
	}
?>