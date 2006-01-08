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

	final class BusinessClassBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();
			
			if ($type = $class->getType())
				$type = $type->toString().' ';
			else
				$type = null;
			
			if ($class->getPattern()->daoExist()) {
				$dao = <<<EOT
		public static function dao()
		{
			return Singletone::getInstance('{$class->getName()}DAO');
		}

EOT;
			} else
				$dao = null;
			
			$out .= <<<EOT
	{$type}class {$class->getName()} extends Auto{$class->getName()}
	{
		public static function create()
		{
			return new {$class->getName()}();
		}
		
{$dao}
		// your brilliant stuff goes here
	}

EOT;
			
			return $out.self::getHeel();
		}
		
		protected static function getHead()
		{
			$head = self::startCap();
			
			$head .=
				' *   This file will never be generated again -'
				.' feel free to edit.            *';

			return $head."\n".self::endCap();
		}
	}
?>