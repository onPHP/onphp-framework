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
/* $Id$ */

	/**
	 * Widely used assertions.
	 * 
	 * @ingroup Base
	**/
	final class Assert extends StaticFactory
	{
		public static function isTrue($boolean, $message = null)
		{
			if ($boolean !== true)
				self::fail(
					$message.', '.self::dumpArgument($boolean)
				);
		}
		
		public static function isFalse($boolean, $message = null)
		{
			if ($boolean !== false)
				self::fail(
					$message.', '.self::dumpArgument($boolean)
				);
		}
		
		public static function isNotFalse($boolean, $message = null)
		{
			if ($boolean === false)
				self::fail(
					$message.', '.self::dumpArgument($boolean)
				);
		}
		
		public static function isNull($variable, $message = null)
		{
			if ($variable !== null)
				self::fail(
					$message.', '.self::dumpArgument($variable)
				);
		}

		public static function isNotNull($variable, $message = null)
		{
			if ($variable === null)
				self::fail($message);
		}

		public static function isArray(&$variable, $message = null)
		{
			if (!is_array($variable))
				self::fail(
					$message.', '.self::dumpArgument($variable)
				);
		}
		
		public static function isNotEmptyArray(&$variable, $message = null)
		{
			self::isArray($variable, $message);
			
			if (!$variable)
				self::fail(
					$message.', '.self::dumpArgument($variable)
				);
		}
		
		public static function isInteger($variable, $message = null)
		{
			if (
				!(
					is_numeric($variable)
					&& $variable == (int) $variable
				)
			)
				self::fail(
					$message.', '.self::dumpArgument($variable)
				);
		}
		
		public static function isPositiveInteger($variable, $message = null)
		{
			if (
				!self::checkInteger($variable)
				|| $variable < 0
			)
				self::fail(
					$message.', '.self::dumpArgument($variable)
				);
		}

		public static function isFloat($variable, $message = null)
		{
			if (
				!(
					$variable == (float) $variable
					&& is_numeric($variable)
				)
			)
				self::fail(
					$message.', '.self::dumpArgument($variable)
				);
		}

		public static function isString($variable, $message = null)
		{
			if (!is_string($variable))
				self::fail(
					$message.', '.self::dumpArgument($variable)
				);
		}
		
		public static function isBoolean($variable, $message = null)
		{
			if (!($variable === true || $variable === false))
				self::fail(
					$message.', '.self::dumpArgument($variable)
				);
		}

		public static function isTernaryBase($variable, $message = null)
		{
			if (
				!(
					($variable === true)
					|| ($variable === false)
					|| ($variable === null)
				)
			)
				self::fail($message);
		}

		public static function brothers(&$first, &$second, $message = null)
		{
			if (get_class($first) !== get_class($second))
				self::fail($message);
		}
		
		public static function isEqual($first, $second, $message = null)
		{
			if ($first !== $second)
				self::fail(
					$message.', '.self::dumpOppositeArguments($first, $second)
				);
		}
		
		public static function isInstance($first, $second, $message = null)
		{
			if (!ClassUtils::isInstanceOf($first, $second))
				self::fail(
					$message.', '.self::dumpOppositeArguments($first, $second)
				);
		}
		
		public static function isUnreachable($message = 'unreachable code reached')
		{
			self::fail($message);
		}
		
		/// exceptionless methods
		//@{
		public static function checkInteger($value)
		{
			return (
				is_numeric($value)
				&& ($value == (int) $value)
				&& (strlen($value) == strlen((int) $value))
			);
		}
		
		public static function dumpArgument($argument)
		{
			return 'argument: ['.print_r($argument, true).']';
		}
		
		public static function dumpOppositeArguments($first, $second)
		{
			return
				'arguments: ['.print_r($first, true).'] '
				.'vs. ['.print_r($second, true).'] ';
		}
		//@}
		
		private static function fail($message = null)
		{
			throw new WrongArgumentException(
				$message
				.(
					defined('__LOCAL_DEBUG__')
						? "\n\n".print_r(debug_backtrace(), true)
						: null
				)
			);
		}
	}
?>