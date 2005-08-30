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

	final class Filter /* Factory */
	{
		public static function text()
		{
			return Singletone::getInstance('TextFilter');
		}
		
		public static function html()
		{
			return Singletone::getInstance('HTMLFilter');
		}
		
		public static function hash()
		{
			return Singletone::getInstance('HashFilter');
		}
	}
?>