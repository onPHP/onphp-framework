<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
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
		const ATTACHED	= 6;
		const SERVER	= 7;
		
		protected $names = array(
			self::GET		=> 'get',
			self::POST		=> 'post',
			self::FILES		=> 'files',
			self::COOKIE	=> 'cookie',
			self::SESSION	=> 'session',
			self::ATTACHED	=> 'attached',
			self::SERVER	=> 'server'
		);
		
		/**
		 * @return RequestType
		**/
		public static function get()
		{
			return new self(self::GET);
		}
		
		/**
		 * @return RequestType
		**/
		public static function post()
		{
			return new self(self::POST);
		}
		
		/**
		 * @return RequestType
		**/
		public static function files()
		{
			return new self(self::FILES);
		}
		
		/**
		 * @return RequestType
		**/
		public static function cookie()
		{
			return new self(self::COOKIE);
		}
		
		/**
		 * @return RequestType
		**/
		public static function session()
		{
			return new self(self::SESSION);
		}

		/**
		 * @return RequestType
		**/
		public static function attached()
		{
			return new self(self::ATTACHED);
		}
		
		/**
		 * @return RequestType
		**/
		public static function server()
		{
			return new self(self::SERVER);
		}
	}
?>