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

	final class BusinessClassBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();
			
			if ($type = $class->getType())
				$typeName = $type->toString().' ';
			else
				$typeName = null;
			
			$interfaces = ' implements Prototyped';
			
			if (!$type || $type->getId() !== MetaClassType::CLASS_ABSTRACT)
				$interfaces .= ', Creatable';
			
			if ($class->getPattern()->daoExist()) {
				$interfaces .= ', DAOConnected';
				$dao = <<<EOT
	public static function dao()
	{
		return Singleton::getInstance('{$class->getName()}DAO');
	}

EOT;
			} else
				$dao = null;
			
			$out .= <<<EOT
{$typeName}class {$class->getName()} extends Auto{$class->getName()}{$interfaces}
{
EOT;

			if (!$type || $type->getId() !== MetaClassType::CLASS_ABSTRACT) {
				$out .= <<<EOT
			
	public static function create()
	{
		return new self;
	}
		
{$dao}
EOT;
			}
			
			$out .= <<<EOT

	public static function proto()
	{
		return Singleton::getInstance('Proto{$class->getName()}');
	}

EOT;

			$out .= <<<EOT

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