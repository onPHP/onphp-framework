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
	final class DictionaryDaoBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();
			
			$out .= <<<EOT
abstract class Auto{$class->getName()}DAO extends StorableDAO
{
	protected \$mapping = array(

EOT;

			$mapping = self::buildMapping($class);
			$pointers = self::buildPointers($class);
			
			$out .= implode(",\n", $mapping);
			
			$className = $class->getName();
			$varName = strtolower($className[0]).substr($className, 1);
			
			$out .= <<<EOT

		);
		
{$pointers}

EOT;
			if ($class->getPattern() instanceof AbstractClassPattern) {
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

EOT;
			} else {
				$out .= <<<EOT

	/**
	 * @return InsertOrUpdateQuery
	**/
	public function setQueryFields(InsertOrUpdateQuery \$query, {$className} \${$varName})

EOT;
			}
			
			$out .= <<<EOT
	{
		return
			\$query->

EOT;
			
			$out .= self::buildFillers($class);
			
			return $out.self::getHeel();
		}
	}
?>