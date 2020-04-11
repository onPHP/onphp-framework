<?php
/***************************************************************************
 *   Copyright (C) 2020 by Dmitriy V. Snezhinskiy                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Meta\Util;

use OnPHP\Core\Base\Instantiatable;
use OnPHP\Core\Base\Singleton;
use OnPHP\Core\Exception\MissingElementException;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Meta\Entity\MetaClass;
use OnPHP\Meta\Pattern\InternalClassPattern;

class MetaClassPull extends Singleton implements Instantiatable {
	
	/**
	 * Namespace separator
	 */
	const NSS = '\\';
	
	/** 
	 * @var array ns+className => MetaClass
	 */
	private $list = array();
	
	/**
	 * @return MetaClassPull
	 */
	public static function me() {
		return Singleton::getInstance(self::class);
	}

	/**
	 * @return array
	 */
	public function getList() {
		return $this->list;
	}
	
	/**
	 * @param MetaClass $class
	 */
	public function addClass(MetaClass $class) {	
		if ($class->getPattern() instanceof InternalClassPattern) {
			$name = $class->getNameWithNS();
		} else {
			$name = $class->getBusinessClass();
		}
		
		$this->list[$name] = $class;
	}
	
	/**
	 * @param string $className
	 * @param boolean $force
	 * @return MetaClass
	 */
	public function getClass($className, $force = true) {
		if (isset($this->list[$className])) {
			return $this->list[$className];
		}
		
		return $this->guessByClassName($className);
	}
	
	/**
	 * @param string $className
	 * @return bool
	 */
	public function hasClass($className) {
		try {
			return (bool)$this->getClass($className);
		} catch (MissingElementException $e) {
			/* do nothing */
		} catch (WrongArgumentException $e) {
			/* do nothing */
		}
			
		return false;
	}
	
	/**
	 * @param string $namespace
	 * @param string $className
	 * @throws MissingElementException
	 * @return MetaClass
	 */
	public function getByNamespaceAndClassName($namespace, $className) {
		$key = $this->makeFullClassName($namespace, $className);
		
		if (!isset($this->list[$key])) {
			throw new MissingElementException("Know nothing about class: {$key}");
		}
		
		return $this->list[$key];
	}

	/**
	 * @param string $className
	 * @throws WrongArgumentException
	 * @return MetaClass
	 */
	public function guessByClassName($className) {
		$found = array();
		$key = self::NSS.$className;
		
		foreach($this->list as $ns => $class) {
			if (substr($ns, -strlen($key)) == $key) {
				$found[$ns] = $class;
			}
		}
		
		if (count($found) > 1) {
			throw new WrongArgumentException("Only one match expected, but "
				. count($found) . " found:\n"
				. print_r($found)
			);
		}
		
		return array_shift($found);
	}
	
	/**
	 * @param string $namespace
	 * @param string $className
	 * @return string
	 */
	private function makeFullClassName($namespace, $className) {
		$namespace = ltrim($namespace, self::NSS);
		$namespace = rtrim($namespace, self::NSS);
		return $namespace . self::NSS . $className;
	}
}

?>