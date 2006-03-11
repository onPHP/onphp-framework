<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Helpers
	**/
	final class RequestType extends Enumeration
	{
		const GET		= 1;
		const POST		= 2;
		const FILES		= 3;
		const COOKIE	= 4;
		const SESSION	= 5;
		
		protected $names = array(
			self::GET		=> 'get',
			self::POST		=> 'post',
			self::FILES		=> 'files',
			self::COOKIE	=> 'cookie',
			self::SESSION	=> 'session'
		);
		
		public static function get()
		{
			return new self(self::GET);
		}
		
		public static function post()
		{
			return new self(self::POST);
		}
		
		public static function files()
		{
			return new self(self::FILES);
		}
		
		public static function cookie()
		{
			return new self(self::COOKIE);
		}
		
		public static function session()
		{
			return new self(self::SESSION);
		}
	}
?>