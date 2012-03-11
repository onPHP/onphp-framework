<?php
/***************************************************************************
 *   Copyright (C) 2012 by Georgiy T. Kutsurua                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Utils
	**/
	class UuidUtils extends StaticFactory
	{

		/**
		 * @static
		 * @return bool
		 */
		public static function isExtensionLoaded()
		{
			return extension_loaded('uuid');
		}

		/**
		 * @static
		 * @param $type
		 * @return string
		 * @throws UnsupportedExtensionException
		 */
		public static function make($type=UUID_TYPE_TIME)
		{
			if(!static::isExtensionLoaded() )
				throw new UnsupportedExtensionException('uuid is unloaded, but it needed!');

			return uuid_create($type);
		}

	}
