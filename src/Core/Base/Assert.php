<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Core\Base;

use OnPHP\Core\Exception\ClassNotFoundException;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Main\Util\ClassUtils;

/**
 * Widely used assertions.
 * @ingroup Base
**/
final class Assert extends StaticFactory
{
	/**
	 * @param $boolean
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isTrue($boolean, string $message = null): void
	{
		if ($boolean !== true)
			throw new WrongArgumentException(
				$message.', '.self::dumpArgument($boolean)
			);
	}

	/**
	 * @param $boolean
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isFalse($boolean, string $message = null): void
	{
		if ($boolean !== false)
			throw new WrongArgumentException(
				$message.', '.self::dumpArgument($boolean)
			);
	}

	/**
	 * @param $boolean
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isNotFalse($boolean, string $message = null): void
	{
		if ($boolean === false)
			throw new WrongArgumentException(
				$message.', '.self::dumpArgument($boolean)
			);
	}

	/**
	 * @param $variable
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isNull($variable, string $message = null): void
	{
		if ($variable !== null)
			throw new WrongArgumentException(
				$message.', '.self::dumpArgument($variable)
			);
	}

	/**
	 * @param $variable
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isEmpty($variable, string $message = null): void
	{
		if (!empty($variable))
			throw new WrongArgumentException(
				$message.', '.self::dumpArgument($variable)
			);
	}

	/**
	 * @param $variable
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isNotEmpty($variable, string $message = null): void
	{
		if (empty($variable))
			throw new WrongArgumentException(
				$message.', '.self::dumpArgument($variable)
			);
	}

	/**
	 * Be careful, when key exists in array and value is null
	 * Assert::isIndexExists not generate WrongArgumentException
	 * @param $array
	 * @param $key
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isIndexExists($array, $key, string $message = null): void
	{
		Assert::isArray($array);

		if (!array_key_exists($key, $array))
			throw new WrongArgumentException(
				$message.', '.self::dumpArgument($key)
			);
	}

	/**
	 * @param $variable
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isNotNull($variable, string $message = null): void
	{
		if ($variable === null)
			throw new WrongArgumentException($message);
	}

	/**
	 * @param $variable
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isScalar($variable, string $message = null): void
	{
		if (!is_scalar($variable))
			throw new WrongArgumentException(
				$message.', '.self::dumpArgument($variable)
			);
	}

	/**
	 * @param $variable
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isArray($variable, string $message = null): void
	{
		if (!is_array($variable))
			throw new WrongArgumentException(
				$message.', '.self::dumpArgument($variable)
			);
	}

	/**
	 * @param $variable
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isNotEmptyArray(&$variable, string $message = null): void
	{
		self::isArray($variable, $message);

		if (!$variable)
			throw new WrongArgumentException(
				$message.', '.self::dumpArgument($variable)
			);
	}

	/**
	 * @param $variable
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isInteger($variable, string $message = null): void
	{
		if (
			!(
				is_numeric($variable)
				&& $variable == (int) $variable
			)
		)
			throw new WrongArgumentException(
				$message.', '.self::dumpArgument($variable)
			);
	}

	/**
	 * @param $variable
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isPositiveInteger($variable, string $message = null): void
	{
		if (
			!self::checkInteger($variable)
			|| $variable < 0
		)
			throw new WrongArgumentException(
				$message.', '.self::dumpArgument($variable)
			);
	}

	/**
	 * @param $variable
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isFloat($variable, string $message = null): void
	{
		if (!self::checkFloat($variable))
			throw new WrongArgumentException(
				$message.', '.self::dumpArgument($variable)
			);
	}

	/**
	 * @param $variable
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isString($variable, string $message = null): void
	{
		if (!is_string($variable))
			throw new WrongArgumentException(
				$message.', '.self::dumpArgument($variable)
			);
	}

	/**
	 * @param $variable
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isBoolean($variable, string $message = null): void
	{
		if ($variable !== true && $variable !== false)
			throw new WrongArgumentException(
				$message.', '.self::dumpArgument($variable)
			);
	}

	/**
	 * @param $variable
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isTernaryBase($variable, string $message = null): void
	{
		if (
			!(
				($variable === true)
				|| ($variable === false)
				|| ($variable === null)
			)
		)
			throw new WrongArgumentException(
				$message.', '.self::dumpArgument($variable)
			);
	}

	/**
	 * @param $first
	 * @param $second
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function brothers($first, $second, string $message = null): void
	{
		if (get_class($first) !== get_class($second))
			throw new WrongArgumentException(
				$message.', '.self::dumpOppositeArguments($first, $second)
			);
	}

	/**
	 * @param $first
	 * @param $second
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isEqual($first, $second, string $message = null): void
	{
		if ($first != $second)
			throw new WrongArgumentException(
				$message.', '.self::dumpOppositeArguments($first, $second)
			);
	}

	/**
	 * @param $first
	 * @param $second
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isNotEqual($first, $second, string $message = null): void
	{
		if ($first == $second)
			throw new WrongArgumentException(
				$message.', '.self::dumpOppositeArguments($first, $second)
			);
	}

	/**
	 * @param $first
	 * @param $second
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isSame($first, $second, string $message = null): void
	{
		if ($first !== $second)
			throw new WrongArgumentException(
				$message.', '.self::dumpOppositeArguments($first, $second)
			);
	}

	/**
	 * @param $first
	 * @param $second
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isNotSame($first, $second, string $message = null): void
	{
		if ($first === $second)
			throw new WrongArgumentException(
				$message.', '.self::dumpOppositeArguments($first, $second)
			);
	}

	/**
	 * @param $first
	 * @param $second
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isTypelessEqual($first, $second, string $message = null): void
	{
		if ($first != $second)
			throw new WrongArgumentException(
				$message.', '.self::dumpOppositeArguments($first, $second)
			);
	}

	/**
	 * @param $first
	 * @param $second
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isLesser($first, $second, string $message = null): void
	{
		if (!($first < $second))
			throw new WrongArgumentException(
				$message.', '.self::dumpOppositeArguments($first, $second)
			);
	}

	/**
	 * @param $first
	 * @param $second
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isGreater($first, $second, string $message = null): void
	{
		if (!($first > $second))
			throw new WrongArgumentException(
				$message.', '.self::dumpOppositeArguments($first, $second)
			);
	}

	/**
	 * @param $first
	 * @param $second
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isLesserOrEqual($first, $second, string $message = null): void
	{
		if (!($first <= $second))
			throw new WrongArgumentException(
				$message.', '.self::dumpOppositeArguments($first, $second)
			);
	}

	/**
	 * @param $first
	 * @param $second
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isGreaterOrEqual($first, $second, string $message = null): void
	{
		if (!($first >= $second))
			throw new WrongArgumentException(
				$message.', '.self::dumpOppositeArguments($first, $second)
			);
	}

	/**
	 * @param $first
	 * @param $second
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isInstance($first, $second, string $message = null): void
	{
		if (!ClassUtils::isInstanceOf($first, $second))
			throw new WrongArgumentException(
				$message.', '.self::dumpOppositeArguments($first, $second)
			);
	}

	/**
	 * @param $left
	 * @param $right
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isSameClasses($left, $right, string $message = null)
	{
		if (!ClassUtils::isSameClassNames($left, $right))
			throw new WrongArgumentException(
				$message.', '.self::dumpOppositeArguments($left, $right)
			);
	}

	/**
	 * @param $className
	 * @param string|null $message
	 * @throws ClassNotFoundException
	 */
	public static function classExists($className, string $message = null): void
	{
		if (!class_exists($className, true))
			throw new ClassNotFoundException(
				$message.', class "'.$className.'" does not exists'
			);
	}

	/**
	 * Checks if the class method exists
	 * @param $object An object instance or a class name
	 * @param string $method The method name
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function methodExists($object, string $method, string $message = null): void
	{
		if (!method_exists($object, $method))
			throw new WrongArgumentException(
				$message.', method "'.get_class($object).'::'.$method.'()" does not exists'
			);
	}

	/**
	 * @param string $message
	 * @throws WrongArgumentException
	 */
	public static function isUnreachable(string $message = 'unreachable code reached'): void
	{
		throw new WrongArgumentException($message);
	}

	/**
	 * @param mixed $object
	 * @param string|null $message
	 * @throws WrongArgumentException
	 */
	public static function isObject($object, string $message = null): void
	{
		if (!is_object($object))
			throw new WrongArgumentException(
				$message.' not object given'
			);
	}

	/**
	 * @param mixed $value
	 * @return bool
	 */
	public static function checkInteger($value): bool
	{
		return (
			is_numeric($value)
			&& ($value == (int) $value)
			&& (strlen($value) == strlen((int) $value))
		);
	}

	/**
	 * @param mixed $value
	 * @return bool
	 */
	public static function checkFloat($value): bool
	{
		return (
			is_numeric($value)
			&& ($value == (float) $value)
		);
	}

	/**
	 * @param mixed $value
	 * @return bool
	 */
	public static function checkScalar($value): bool
	{
		return is_scalar($value);
	}

	/**
	 * @param mixed $argument
	 * @return string
	 */
	public static function dumpArgument($argument): string
	{
		return 'argument: ['.print_r($argument, true).']';
	}

	/**
	 * Dump two arguments as string
	 * @param mixed $first
	 * @param mixed $second
	 * @return string
	 */
	public static function dumpOppositeArguments($first, $second): string
	{
		return
			'arguments: ['.print_r($first, true).'] '
			.'vs. ['.print_r($second, true).'] ';
	}
}