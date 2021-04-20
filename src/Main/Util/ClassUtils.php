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
	 * Copy only public and protected object properties fetch
	 * allowed by get... methods and set by set... methods.
	 * Be careful, private properties from parent class not copied!
	 * @param object $source
	 * @param object $destination
	 * @throws WrongArgumentException
	 * @throws ReflectionException
	 */
	public static function copyProperties(object $source, object $destination): void
	{
		Assert::isSameClasses(get_class($source), get_class($destination));

		$class = new ReflectionClass($source);

		foreach ($class->getProperties() as $property) {
			$name = ucfirst($property->getName());
			$getter = 'get'.$name;
			$setter = 'set'.$name;

			if (
				!method_exists($source, $getter)
				|| !is_callable([$source, $getter])
				|| !method_exists($destination, $setter)
				|| !is_callable([$destination, $setter])
			) {
				continue;
			}

			$sourceValue = $source->$getter();
			if ($sourceValue !== null) {
				$destination->$setter($sourceValue);
			} else {
				$dropper = 'drop'.$name;

				if (
					($firstArg = $class->getMethod($setter)->getParameters()[0] ?? null) !== null
					&& $firstArg->allowsNull() === true
				) {
					$destination->$setter($sourceValue);
				} elseif (
					method_exists($destination, $dropper)
					&& is_callable([$destination, $dropper])
				) {
					$destination->$dropper();
				}
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
				method_exists($source, $getter)
				&& is_callable([$source, $getter])
				&& method_exists($destination, $setter)
				&& is_callable([$destination, $setter])
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
				method_exists($source, $getter)
				&& is_callable([$source, $getter])
				&& method_exists($destination, $setter)
				&& is_callable([$destination, $setter])
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
	public static function isClassName($className): bool
	{
		if (!is_string($className)) {
			return false;
		}

		return preg_match('/^'.self::CLASS_NAME_PATTERN.'$/', $className) === 1;
	}

	/**
	 * @param mixed $left
	 * @param mixed $right
	 * @return bool
	 * @throws WrongArgumentException
	 */
	public static function isSameClassNames($left, $right): bool
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
	 * @todo remove use function class_implemets in future, it`s removed in PHP 8
	 */
	public static function isClassImplements($what, bool $autoload = true): array
	{
		static $classImplements = null;

		if (null === $classImplements) {
			if (function_exists('class_implements')) {
				$classImplements = 'class_implements';
			} else {
				$classImplements = function ($what, bool $autoload) {
					$interfacesList = (new ReflectionClass($what))
						->getInterfaceNames();
					return array_combine($interfacesList, $interfacesList);
				};
			}
		}

		try {
			$implements = $classImplements($what, $autoload);
		} catch(\Throwable $exception) {
			throw new ClassNotFoundException($what);
		}

		return is_array($implements) ? $implements : [];
	}

	/**
	 * @param mixed $object
	 * @param mixed $class
	 * @return bool
	 * @throws WrongArgumentException
	 */
	public static function isInstanceOf($object, $class): bool
	{
		if (is_object($class)) {
			if (is_object($object)) {
				return $object instanceof $class;
			} elseif (is_string($object)) {
				return is_a($object, get_class($class), true);
			}
		} elseif (
			is_string($class)
			&& (is_object($object) || is_string($object))
		) {
			return is_a($object, $class, true);
		}

		Assert::isUnreachable('strange object or class given ' . Assert::dumpOppositeArguments($object, $class));
	}

	/**
	 * @param mixed $methodSignature
	 * @param ...$arguments
	 * @return mixed
	 * @throws ClassNotFoundException
	 * @throws WrongArgumentException
	 */
	public static function callStaticMethod($methodSignature, ...$arguments)
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
	public static function checkStaticMethod($methodSignature): array
	{
		if (is_array($methodSignature)) {
			$nameParts = $methodSignature;
		} elseif (is_string($methodSignature)) {
			$nameParts = explode('::', $methodSignature);
		} else {
			Assert::isUnreachable('Incorrect method signature ' . gettype($methodSignature));
		}
		
		Assert::isEqual(
			count($nameParts),
			2,
			'Incorrect method signature ' . gettype($methodSignature)
		);
		
		list($className, $methodName) = $nameParts;

		try {
			$class = new ReflectionClass($className);
		} catch (ReflectionException $exception) {
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
}