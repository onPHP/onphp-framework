<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Builders
	**/
	final class DictionaryDaoBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();
			
			$out .= <<<EOT
abstract class Auto{$class->getName()}DAO extends ComplexBuilderDAO
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

EOT;

			$hints = self::buildHints($class);
			
			if (0 /* $hints */) {
				$classes = implode(",\n", $hints);
				
				$out .= <<<EOT

	protected \$classes = array(
		{$classes}
	);

EOT;
			}
			
			$out .= <<<EOT
		
{$pointers}

EOT;
			if ($class->getPattern() instanceof AbstractClassPattern) {
				$out .= <<<EOT

	/**
	 * @return InsertOrUpdateQuery
	**/
	public function setQueryFields(InsertOrUpdateQuery \$query, /* {$className} */ \${$varName})

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