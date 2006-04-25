<?php
/***************************************************************************
 *   Copyright (C) 2006 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Utils
	**/
	final class TextUtils extends StaticFactory
	{
		public static function friendlyFileSize($size, $order = 0)
		{
			static $units = array('', 'k' , 'm', 't', 'p');
			
			if ($size >= 1024 && $order < 4)
				return self::friendlyFileSize($size / 1024, $order + 1);
			elseif (isset($units[$order]))
				return round($size, 2).$units[$order];
				
			return $size;
		}
	}
?>