<?php
/***************************************************************************
 *   Copyright (C) 2017 by Alex Gorbylev                                   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Meta\Entity;

use OnPHP\Core\Base\StaticFactory;
use OnPHP\Core\Exception\MissingElementException;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Meta\Util\NamespaceUtils;
use OnPHP\Meta\Pattern\InternalClassPattern;
use OnPHP\Meta\Type\ObjectType;
use OnPHP\Meta\Util\UsesPull;

class MetaClassNameBuilder extends StaticFactory {

	/**
	 * @param MetaClass $class
	 * @param bool      $addBackslash
	 *
	 * @return string
	 * @throws WrongArgumentException
	 */
	public static function getClassOfMetaClass(MetaClass $class, bool $addBackslash = false)
	{
		if ($class->getPattern() instanceof InternalClassPattern) {
			throw new WrongArgumentException();
		} else {
			return ($addBackslash ? '\\' : '') 
				. NamespaceUtils::getBusinessNS($class) 
				. '\\' . $class->getName();
		}
	}

	/**
	 * @param MetaClassProperty $property
	 * @param bool              $addBackslash
	 *
	 * @return string
	 * @throws WrongArgumentException
	 */
	public static function getClassOfMetaProperty(
		MetaClassProperty $property, 
		bool $addBackslash = false
	) {
		$type = $property->getType();
		
		if (!($type instanceof ObjectType)) {
			throw new WrongArgumentException();
		}
		
		$className = $addBackslash ? '\\' : '';
		
		if ($type->isGeneric()) {
			try {
				$className = UsesPull::me()->getImport($property->getType()->getClassName(), true);
			} catch (MissingElementException $e){
				$className .= $property->getType()->getFullClass();
			}
		} else {
			if ($type->getClassName()[0] == '\\') {
				$className = $type->getClassName();
			} else {
				try {
					$className = UsesPull::me()->getImport($property->getType()->getClassName(), true);
				} catch (MissingElementException $e){
					$className .=
						self::guessFullClass($property->getClass(), $type->getClassName());
				}
			}
		}
		
		return $className;
	}

	/**
	 * @param MetaClassProperty $property
	 * @param bool              $addBackslash
	 *
	 * @return string
	 * @throws WrongArgumentException
	 */
	public static function getContainerClassOfMetaProperty(
		MetaClassProperty $property,
		bool $addBackslash = false
	) {
		if (
			!($property->getType() instanceof ObjectType)
			|| is_null($property->getRelationId())
		) {
			throw new WrongArgumentException();
		}
		
		return  ($addBackslash ? '\\' : '')
			.$property->getClass()->getDaoNamespace()
			.'\\'
			.$property->getClass()->getName() 
			. ucfirst($property->getName()) 
			. 'DAO';
	}

	private static function guessFullClass(
		MetaClass $source, 
		string $targetClassName
	) {
		$nsmap = MetaConfiguration::me()->getNamespaceList();
		$nearest = $nsmap[$source->getNamespace()];
		
		if (in_array($targetClassName, $nearest['classes'])) {
			return $source->getNamespace() 
				. ($nearest['build']?'\\'.'Business':'') 
				. '\\' 
				. $targetClassName;
		}
		
		foreach ( $nsmap as $namespace=>$info ) {
			if (in_array($targetClassName, $info['classes'])) {
				return $namespace 
					. ($info['build']?'\\'.'Business':'') 
					. '\\' 
					. $targetClassName;
			}
		}
		
		throw new MissingElementException("class `{$targetClassName}` was not found in any namespace");
	}

}
?>