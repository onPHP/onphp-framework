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
	final class ValueObjectDaoBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			$className = $class->getName();
			$varName = strtolower($className[0]).substr($className, 1);
			
			$out = self::getHead();
			
			$out .= <<<EOT
abstract class Auto{$class->getName()}DAO extends ValueObjectDAO
{
EOT;
			if (sizeof($class->getProperties())) {

				$out .= <<<EOT
	/**
	 * @return InsertOrUpdateQuery
	**/
	public function setQueryFields(InsertOrUpdateQuery \$query, {$className} \${$varName})
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