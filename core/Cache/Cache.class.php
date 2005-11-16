<?php
/***************************************************************************
 *   Copyright (C) 2005 by Anton E. Lebedevich, Konstantin V. Arkhipov     *
 *   noiselist@pochta.ru, voxus@gentoo.org                                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */
	
	/**
	 * System-wide access to selected CachePeer.
	 *
	 * @see CachePeer
	**/
	final class Cache extends StaticFactory
	{
		const NOT_FOUND			= 'nil';

		const EXPIRES_FOREVER	= 259200; // 3 days
		const EXPIRES_MAXIMUM	= 21600; // 6 hrs
		const EXPIRES_MEDIUM	= 3600; // 1 hr
		const EXPIRES_MINIMUM	= 300; // 5 mins
		
		const DO_NOT_CACHE		= -2005;
		
		private static $peer	= null;
		
		public static function me()
		{
			if (!self::$peer || !self::$peer->isAlive())
				self::$peer = new ReferencePool(new RuntimeMemory());
			
			return self::$peer;
		}

		public static function setPeer(CachePeer $peer)
		{
			self::$peer = $peer;
		}
	}
?>