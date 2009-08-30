<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
	
	final class WmlLanguage extends XmlLanguage
	{
		const VER_1_1	= 11;
		const VER_1_3	= 13;
		
		protected $commonName	= 'wml';
		
		protected $versions		= array(
			self::VER_1_1	=> true,
			self::VER_1_3	=> true
		);
		
		protected $version		= self::VER_1_3;
		
		/**
		 * @return WmlLanguage
		**/
		public static function create()
		{
			return new self;
		}
	}
?>