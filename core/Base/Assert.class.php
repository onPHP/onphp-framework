<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class Assert
	{
		public static function isTrue($boolean, $message = null)
		{
			if ($boolean !== true)
				throw new WrongArgumentException($message);
		}

		public static function isFalse($boolean, $message = null)
		{
			self::isTrue(!$boolean);
		}

		public static function isArray(&$variable, $message = null)
		{
			if (!is_array($variable))
				throw new WrongArgumentException($message);
		}

		public static function isInteger($variable, $message = null)
		{
			if (
				!(
					is_numeric($variable) &&
					$variable == (int) $variable &&
					strlen($variable) == strlen((int) $variable)
				)
			)
				throw new WrongArgumentException($message);
		}

		public static function isString(&$variable, $message = null)
		{
			if (!is_string($variable))
				throw new WrongArgumentException($message);
		}
		
		public static function isBoolean(&$variable, $message = null)
		{
			if (!($variable === true || $variable === false))
				throw new WrongArgumentException($message);
		}

		public static function isTernaryBase(&$variable, $message = null)
		{
			if (!
				(
					($variable === true) ||
					($variable === false) ||
					($variable === null)
				)
			)
				throw new WrongArgumentException($message);
		}

		public static function brothers(&$first, &$second, $message = null)
		{
			if (get_class($first) !== get_class($second))
				throw new WrongArgumentException($message);
		}
	}
?>