<?php
/***************************************************************************
 *   Copyright (C) 2008 by Tsyrulnik Y. Viatcheslav                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class GoogleGeoAddressAccuracyLevel extends Enumeration
	{
		// FIXME: it's a bad practice to use zero as enumeration's value
		const LEVEL_0 = 0;
		const LEVEL_1 = 1;
		const LEVEL_2 = 2;
		const LEVEL_3 = 3;
		const LEVEL_4 = 4;
		const LEVEL_5 = 5;
		const LEVEL_6 = 6;
		const LEVEL_7 = 7;
		const LEVEL_8 = 8;
		const LEVEL_9 = 9;
		
		protected $names = array(
			self::LEVEL_0 => 'Unknown location.',
			self::LEVEL_1 => 'Country level.',
			self::LEVEL_2 => 'Region (state, province, prefecture, etc.) level.',
			self::LEVEL_3 => 'Sub-region (county, municipality, etc.) level',
			self::LEVEL_4 => 'Town (city, village) level',
			self::LEVEL_5 => 'Post code (zip code) level',
			self::LEVEL_6 => 'Street level',
			self::LEVEL_7 => 'Intersection level',
			self::LEVEL_8 => 'Address level',
			self::LEVEL_9 => 'Premise (building name, property name, shopping center, etc.) level'
		);
	}
?>