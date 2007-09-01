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
	final class BusinessClassBuilder extends OnceBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();
			
			if ($type = $class->getType())
				$typeName = $type->toString().' ';
			else
				$typeName = null;
			
			$interfaces = ' implements Prototyped';
			
			if (
				$class->getPattern()->daoExists()
				&& (!$class->getPattern() instanceof AbstractClassPattern)
			) {
				if (!$class->getPattern() instanceof ValueObjectPattern)
					$interfaces .= ', DAOConnected';
				
				$daoName = $class->getName().'DAO';
				$dao = <<<EOT
	/**
	 * @return {$daoName}
	**/
	public static function dao()
	{
		return Singleton::getInstance('{$daoName}');
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

	/**
	 * @return {$class->getName()}
	**/
	public static function create()
	{
		return new self;
	}
		
{$dao}
EOT;
				$protoName = 'Proto'.$class->getName();
			
				$out .= <<<EOT

	/**
	 * @return {$protoName}
	**/
	public static function proto()
	{
		return Singleton::getInstance('{$protoName}');
	}

EOT;

			}
			
			$out .= <<<EOT

	// your brilliant stuff goes here
}

EOT;
			return $out.self::getHeel();
		}
	}
?>