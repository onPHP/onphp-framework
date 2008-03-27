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
				$customCreate = null;
				
				if (
					$class->getFinalParent()->getPattern()
						instanceof InternalClassPattern
				) {
					$parent = $class;
					
					while ($parent = $parent->getParent()) {
						$info = new ReflectionClass($parent->getName());
						
						if (
							$info->hasMethod('create')
							&& ($info->getMethod('create')->getParameters() > 0)
						) {
							$customCreate = true;
							break;
						}
					}
				}
				
				if ($customCreate) {
					$creator = $info->getMethod('create');
					
					$declaration = array();
					
					foreach ($creator->getParameters() as $parameter) {
						$declaration[] =
							'$'.$parameter->getName()
							// no one can live without default value @ ::create
							.' = '
							.(
								$parameter->getDefaultValue()
									? $parameter->getDefaultValue()
									: 'null'
							);
					}
					
					$declaration = implode(', ', $declaration);
					
					$out .= <<<EOT

	/**
	 * @return {$class->getName()}
	**/
	public static function create({$declaration})
	{
		return new self({$declaration});
	}
		
EOT;
				} else {
					$out .= <<<EOT

	/**
	 * @return {$class->getName()}
	**/
	public static function create()
	{
		return new self;
	}
		
EOT;
				}
				
				$protoName = 'Proto'.$class->getName();
			
				$out .= <<<EOT

{$dao}
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