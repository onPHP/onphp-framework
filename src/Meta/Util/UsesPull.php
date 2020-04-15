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

class UsesPull extends Singleton implements Instantiatable {
	
	/**
	 * Namespace separator
	 */
	const NSS = '\\';
	
	/**
	 * @var array ns+className => MetaClass
	 */
	private $list = array();

	/**
	 * @return UsesPull
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
	 * @param string $className
	 * @param string $import
	 */
	public function addClass($className, $import) {
		$this->list[$className] = $import;
	}
	
	/**
	 * @param string $className
	 * @param boolean $addBackSlash
	 * @throws MissingElementException
	 * @return string
	 */
	public function getImport($className, $addBackSlash = false) {
		if (!isset($this->list[$className])) {
			throw new MissingElementException("Knows nothing about class: {$className}");
		}
		
		return $addBackSlash ? self::NSS . $this->list[$className] : $this->list[$className];
	}
	
	/**
	 * @param string $className
	 * @return bool
	 */
	public function hasClass($className) {
		return isset($this->list[$className]);
	}
}

