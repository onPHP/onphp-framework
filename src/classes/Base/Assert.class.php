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
		public static function isArray(&$variable)
		{
			if (!is_array($variable))
				throw new WrongArgumentException();
		}
		
		public static function isInteger(&$variable)
		{
			if (
				!(
					is_numeric($variable) &&
					$variable == (int) $variable &&
					strlen($variable) == strlen((int) $variable)
				)
			)
				throw new WrongArgumentException();
		}
		
		public static function isString(&$variable)
		{
			if (!is_string($variable))
				throw new WrongArgumentException();
		}
		
		public static function isTernaryBase(&$variable)
		{
			if (!
				(
					($variable === true) ||
					($variable === false) ||
					($variable === null)
				)
			)
				throw new WrongArgumentException();
		}
	}
?>