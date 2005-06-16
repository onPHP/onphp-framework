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

	class Rules /* Factory */
	{
		public static function block($name)
		{
			return new BlockRule($name);
		}
		
		public static function depend($name)
		{
			return new DependRule($name);
		}
		
		public static function exclude($name)
		{
			return new ExcludeRule($name);
		}
	}
?>