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

namespace OnPHP\Meta\Util;

use OnPHP\Core\Exception\MissingElementException;
use OnPHP\Meta\Entity\MetaClass;
use OnPHP\Meta\Entity\MetaConfiguration;

class NamespaceUtils {

	public static function checkNS($namespace) {
		$spaces = MetaConfiguration::me()->getNamespaceList();

		$exist = false;
		foreach($spaces as $key=>$_) {
			if( strpos($namespace, $key) === 0 ) {
				$exist = true;
				break;
			}
		}

		if( $exist==false ) {
			throw new MissingElementException("knows nothing about '{$namespace}' namespace");
		}

		return true;
	}

	public static function getBusinessNS(MetaClass $class, $auto = false) {
		return self::getNamespace($class->getNamespace(), 'Business', $auto);
	}

	public static function getDAONS(MetaClass $class, $auto = false) {
		return self::getNamespace($class->getNamespace(), 'DAO', $auto);
	}

	public static function getProtoNS(MetaClass $class, $auto = false) {
		return self::getNamespace($class->getNamespace(), 'Proto', $auto);
	}

	public static function getBusinessClass(MetaClass $class, $auto = false,  $full = true) {
		$className = $full ? self::getNamespace($class->getNamespace(), 'Business', $auto).'\\' : '';
		if( $auto ) {
			$className .= 'Auto';
		}
		$className .= $class->getName();
		return $className;
	}

	public static function getDAOClass(MetaClass $class, $auto = false,  $full = true) {
		$className = $full ? self::getNamespace($class->getNamespace(), 'DAO', $auto).'\\' : '';
		if( $auto ) {
			$className .= 'Auto';
		}
		$className .= $class->getName().'DAO';
		return $className;
	}

	public static function getProtoClass(MetaClass $class, $auto = false,  $full = true) {
		$className = $full ? self::getNamespace($class->getNamespace(), 'Proto', $auto).'\\' : '';
		if( $auto ) {
			$className .= 'Auto';
		}
		$className .= 'Proto'.$class->getName();
		return $className;
	}

	public static function getBusinessPath(MetaClass $class, $auto = false) {
		return self::getPath($class->getNamespace(), 'Business', self::getBusinessClass($class, $auto, false), $auto);
	}

	public static function getDAOPath(MetaClass $class, $auto = false) {
		return self::getPath($class->getNamespace(), 'DAO', self::getDAOClass($class, $auto, false), $auto);
	}

	public static function getProtoPath(MetaClass $class, $auto = false) {
		return self::getPath($class->getNamespace(), 'Proto', self::getProtoClass($class, $auto, false), $auto);
	}

	public static function getDAODir(MetaClass $class) {
		return self::getDir($class->getNamespace(), 'DAO', false);
	}

	private static function getNamespace($namespace, $type, $auto) {
		$parts = [];
		if( $auto ) {
			$parts[] = 'Auto';
		}
		$parts[] = $type;

		return $namespace.'\\'.implode('\\', $parts);
	}

	private static function getPath($namespace, $type, $name, $auto) {
		$path = self::getDir($namespace, $type, $auto);
		$path .= DIRECTORY_SEPARATOR;
		$path .= $name;
		$path .= EXT_CLASS;

		return $path;
	}

	private static function getDir($namespace, $type, $auto) {
		$spaces = MetaConfiguration::me()->getNamespaceList();

		if( !isset($spaces[$namespace]) ) {
			throw new MissingElementException("knows nothing about '{$namespace}' namespace");
		}

		$nsparts = [$spaces[$namespace]['path']];
		if( $auto ) {
			$nsparts[] = 'Auto';
		}
		$nsparts[] = $type;

		return implode(DIRECTORY_SEPARATOR, $nsparts);
	}

}