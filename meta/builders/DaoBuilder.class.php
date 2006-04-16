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
	final class DaoBuilder extends OnceBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();
			
			$type = $class->getType();
			
			if ($type && $type->getId() == MetaClassType::CLASS_ABSTRACT) {
				$abstract = 'abstract ';
				$notes = 'nothing';
			} else {
				$abstract = null;
				$notes = 'your brilliant stuff goes here';
			}
			
			$out .= <<<EOT
{$abstract}class {$class->getName()}DAO extends Auto{$class->getName()}DAO
{
	// {$notes}
}

EOT;
			
			return $out.self::getHeel();
		}
	}
?>