<?php
/***************************************************************************
 *   Copyright (C) 2004-2009 by Sveta A. Smirnova                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Core\Base;

use OnPHP\Core\Exception\MissingElementException;

/**
 * Inheritable Singleton's pattern implementation.
 * 
 * @ingroup Base
 * @ingroup Module
**/
abstract class Singleton
{
	private static array $instances = [];

	protected function __construct() {/* you can't create me */}

	/**
	 * @param string $class
	 * @param ...$args
	 * @return object
	 * @throws MissingElementException
	 * @throws \OnPHP\Core\Exception\WrongArgumentException
	 */
	final public static function getInstance(string $class, ...$args): object
	{
		if (!isset(self::$instances[$class])) {
			// for Singleton::getInstance('class_name', $arg1, ...) calling
			if (1 < count($args)) {
				// emulation of ReflectionClass->newInstanceWithoutConstructor
				$object =
					unserialize(
						sprintf('O:%d:"%s":0:{}', strlen($class), $class)
					);

				call_user_func_array(
					array($object, '__construct'),
					$args
				);
			} else {
				try {
					$object =
						$args
							? new $class(...$args)
							: new $class();
				} catch (\ArgumentCountError $exception) {
					throw new MissingElementException('Too few arguments to __constructor');
				}
			}

			Assert::isTrue(
				$object instanceof Singleton,
				"Class '{$class}' is something not a Singleton's child"
			);

			self::$instances[$class] = $object;
		}

		return self::$instances[$class];
	}

	/**
	 * @return object[]
	 */
	final public static function getAllInstances(): array
	{
		return self::$instances;
	}

	/**
	 * @param string $class
	 * @throws MissingElementException
	 */
	final public static function dropInstance(string $class): void
	{
		if (!isset(self::$instances[$class]))
			throw new MissingElementException('knows nothing about '.$class);

		unset(self::$instances[$class]);
	}

	final public function __clone() {/* do not clone me */}
	final public function __sleep() {/* restless class */}
}