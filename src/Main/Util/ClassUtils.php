<?php
/***************************************************************************
 *   Copyright (C) 2007-2009 by Dmitry E. Demidov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Util;

use ReflectionClass;
use ReflectionException;
use OnPHP\Core\Base\Assert;
use OnPHP\Core\Base\StaticFactory;
use OnPHP\Core\Exception\ClassNotFoundException;
use OnPHP\Core\Exception\WrongArgumentException;

/**
 * @ingroup Utils
**/
final class ClassUtils extends StaticFactory
{
	const CLASS_NAME_PATTERN = '(\\\\?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)+';

	/**
	 * @param object $source
	 * @param object $destination
	 * @throws WrongArgumentException
	 */
	public static function copyProperties(object $source, object $destination): void
	{
		Assert::isEqual(get_class($source), get_class($destination));

		$class = new ReflectionClass($source);

		foreach ($class->getProperties() as $property) {
			$name = ucfirst($property->getName());
			$getter = 'get'.$name;
			$setter = 'set'.$name;

			if (
				!$class->hasMethod($getter)
				|| !$class->getMethod($getter)->isPublic()
				|| !$class->hasMethod($setter)
				|| !$class->getMethod($setter)->isPublic()
			) {
				continue;
			}

			$sourceValue = $source->$getter();

			if (
				$sourceValue !== null
				|| ($class->getMethod($setter)->getParameters()[0] ?? null)?->allowsNull() === true
			) {
				$destination->$setter($sourceValue);
			}
		}
	}

	/**
	 * @param object $source
	 * @param object $destination
	 * @throws WrongArgumentException
	 */
	public static function copyNotNullProperties(object $source, object $destination): void
	{
		Assert::isEqual(get_class($source), get_class($destination));

		$class = new ReflectionClass($source);

		foreach ($class->getProperties() as $property) {
			$name = ucfirst($property->getName());
			$getter = 'get'.$name;
			$setter = 'set'.$name;

			if (
				$class->hasMethod($getter)
				&& $class->getMethod($getter)->isPublic()
				&& $class->hasMethod($setter)
				&& $class->getMethod($setter)->isPublic()
				&& ($value = $source->$getter()) !== null
			) {
				$destination->$setter($value);
			}
		}
	}

	/**
	 * @param object $source
	 * @param object $destination
	 * @throws WrongArgumentException
	 */
	public static function fillNullProperties(object $source, object $destination): void
	{
		Assert::isEqual(get_class($source), get_class($destination));

		$class = new ReflectionClass($source);

		foreach ($class->getProperties() as $property) {
			$name = ucfirst($property->getName());
			$getter = 'get'.$name;
			$setter = 'set'.$name;

			if (
				$class->hasMethod($getter)
				&& $class->getMethod($getter)->isPublic()
				&& $class->hasMethod($setter)
				&& $class->getMethod($setter)->isPublic()
				&& null === $destination->$getter()
				&& (
					null !== ($sourceValue = $source->$getter())
				)
			) {
				$destination->$setter($sourceValue);
			}
		}
	}

	/**
	 * @param mixed $className
	 * @return bool
	 */
	public static function isClassName(mixed $className): bool
	{
		if (!is_string($className)) {
			return false;
		}

		return preg_match('/^'.self::CLASS_NAME_PATTERN.'$/', $className) > 0;
	}

	/**
	 * @param mixed $left
	 * @param mixed $right
	 * @return bool
	 * @throws WrongArgumentException
	 */
	public static function isSameClassNames(mixed $left, mixed $right): bool
	{
		if (
			(
				(is_object($left) && ($left = get_class($left))) 
				|| (self::isClassName($left) && class_exists($left))
			)
			&& (
				(is_object($right) && ($right = get_class($right)))
				|| (self::isClassName($right) && class_exists($right))
			)
		) {
			return '\\'.ltrim($left, '\\') == '\\'.ltrim($right, '\\');
		}
		
		throw new WrongArgumentException(
			'strange class given '
			. Assert::dumpOppositeArguments($left, $right)
		);
	}

	/**
	 * @param mixed $what
	 * @param bool $autoload
	 * @return array
	 * @throws ClassNotFoundException
	 */
	public static function isClassImplements(mixed $what, bool $autoload = true): array
	{
		static $classImplements = null;

		if (null === $classImplements) {
			if (function_exists('class_implements')) {
				$classImplements = 'class_implements';
			} else {
				$classImplements = function(mixed $what, bool $autoload) {
					try {
						$info = new ReflectionClass($what);
					} catch (ReflectionException) {
						throw new ClassNotFoundException($what);
					}
					return $info->getInterfaceNames();
				};
			}
		}

		$implements = $classImplements($what, $autoload);

		return is_array($implements) ? $implements : [];
	}

	/**
	 * @param mixed $object
	 * @param mixed $class
	 * @return bool
	 * @throws WrongArgumentException
	 */
	public static function isInstanceOf(mixed $object, mixed $class): bool
	{
		if (is_object($class)) {
			$className = get_class($class);
		} elseif (is_string($class)) {
			$className = $class;
		} else {
			Assert::isUnreachable('strange class given ' . Assert::dumpArgument($object));
		}
		
		$className = '\\'.ltrim($className, '\\');

		if (is_string($object)) {
			$object = '\\'.ltrim($object, '\\');
			
			if (self::isClassName($object)) {
				return
					$object == $className
					|| is_subclass_of($object, $className)
					|| in_array($class, self::isClassImplements($object));
			}
		} elseif (is_object($object)) {
			return $object instanceof $className;
		}
			
		throw new WrongArgumentException('strange object given');
	}

	/**
	 * @param mixed $methodSignature
	 * @param ...$arguments
	 * @return mixed
	 * @throws ClassNotFoundException
	 * @throws WrongArgumentException
	 */
	public static function callStaticMethod(mixed $methodSignature, ...$arguments): mixed
	{
		return
			call_user_func_array(
				self::checkStaticMethod($methodSignature),
				$arguments
			);
	}

	/**
	 * @param mixed $methodSignature
	 * @return array
	 * @throws ClassNotFoundException
	 * @throws WrongArgumentException
	 */
	public static function checkStaticMethod(mixed $methodSignature): array
	{
		if (is_array($methodSignature)) {
			$nameParts = $methodSignature;
		} elseif (is_string($methodSignature)) {
			$nameParts = explode('::', $methodSignature);
		} else {
			Assert::isUnreachable('Incorrect method signature ' . Assert::dumpArgument($methodSignature));
		}
		
		Assert::isEqual(
			count($nameParts),
			2,
			'Incorrect method signature ' . Assert::dumpArgument($methodSignature)
		);
		
		list($className, $methodName) = $nameParts;

		try {
			$class = new ReflectionClass($className);
		} catch (ReflectionException) {
			throw new ClassNotFoundException($className);
		}

		Assert::isTrue(
			$class->hasMethod($methodName),
			"knows nothing about '{$className}::{$methodName}' method"
		);

		$method = $class->getMethod($methodName);

		Assert::isTrue(
			$method->isStatic(),
			"method is not static '{$className}::{$methodName}'"
		);

		Assert::isTrue(
			$method->isPublic(),
			"method is not public '{$className}::{$methodName}'"
		);

		return $nameParts;
	}

	/**
	 * @deprecated
	 */
	public static function preloadAllClasses(): void
	{
		foreach (explode(PATH_SEPARATOR, get_include_path()) as $directory) {
			foreach (
				glob(
					$directory.DIRECTORY_SEPARATOR.'/*'.EXT_CLASS,
					GLOB_NOSORT
				)
				as $file
			) {
				$className = basename($file, EXT_CLASS);

				if (
					!class_exists($className, false)
					&& !interface_exists($className, false)
					&& !trait_exists($className, false)
				) {
					include $file;
				}
			}
		}
	}
}