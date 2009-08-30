<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

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