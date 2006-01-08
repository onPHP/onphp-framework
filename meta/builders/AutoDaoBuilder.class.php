<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class AutoDaoBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();
			
			if ($parent = $class->getParent())
				$parent = $parent->getName().'DAO';
			else
				$parent = 'MappedStorableDAO';
			
			$out .=
				"\tabstract class Auto{$class->getName()}DAO extends {$parent}\n"
				."\t{\n";
			
			// bah.
			
			$out .= "\t}\n";
			$out .= self::getHeel();
			
			return $out;
		}
	}
?>