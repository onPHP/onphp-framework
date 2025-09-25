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

namespace OnPHP\Meta\Builder;

use OnPHP\Core\Base\Prototyped;
use OnPHP\Core\Base\Singleton;
use OnPHP\Main\DAO\DAOConnected;
use OnPHP\Meta\Entity\MetaClass;
use OnPHP\Meta\Pattern\AbstractClassPattern;
use OnPHP\Meta\Entity\MetaClassType;
use OnPHP\Meta\Pattern\InternalClassPattern;
use OnPHP\Meta\Util\NamespaceUtils;

	/**
	 * @ingroup Builders
	**/
	final class BusinessClassBuilder extends OnceBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();
			
			$uses = [Singleton::class, $class->getAutoBusinessClass()];
			
			if ($type = $class->getType()) {
				$typeName = $type->toString() . ' ';
			} else {
				$typeName = null;
			}
			
			$interfaces = ' implements Prototyped';
			$uses[] = Prototyped::class;
			$uses[] = $class->getProtoClass();
			
			if (
				$class->getPattern()->daoExists()
				&& (!$class->getPattern() instanceof AbstractClassPattern)
			) {
				$interfaces .= ', DAOConnected';
				$uses[] = DAOConnected::class;
				$uses[] = $class->getDaoClass();
				
				$daoName = $class->getName().'DAO';
				$dao = <<<EOT
	/**
	 * @return {$daoName}
	**/
	public static function dao()
	{
		return Singleton::getInstance({$daoName}::class);
	}

EOT;
			} else {
				$dao = null;
			}
			
			$namespace = NamespaceUtils::getBusinessNS($class);
			$out .= "namespace {$namespace};\n\n";
			
			foreach($uses as $import) {
				$out .= "use $import;\n";
			}
			
			$out .= "\n";
			
			$out .= <<<EOT
{$typeName}class {$class->getName()} extends Auto{$class->getName()}{$interfaces}
{
EOT;

			if (!$type || $type->getId() !== MetaClassType::CLASS_ABSTRACT) {
				$buildCreate = true;
				
				if (
					$class->getFinalParent()->getPattern()
						instanceof InternalClassPattern
				) {
					$parent = $class;
					
					while ($parent = $parent->getParent()) {
						$info = new \ReflectionClass($parent->getNameWithNS());
						
						if ($info->hasMethod('create')) {
							$buildCreate = false;
							break;
						}
					}
				}
				
				if ($buildCreate) {
					$out .= <<<EOT

	/**
	 * @return static
	**/
	public static function create(): {$class->getName()}
	{
		return new static;
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
		return Singleton::getInstance({$protoName}::class);
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