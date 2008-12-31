<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Http
	**/
	final class HttpMethod extends Enumeration
	{
		const OPTIONS	= 1;
		const GET		= 2;
		const HEAD 		= 3;
		const POST		= 4;
		const PUT		= 5;
		const DELETE	= 6;
		const TRACE		= 7;
		const CONNECT	= 8;
		
		protected $names = array(
			self::OPTIONS 	=> 'OPTIONS',
			self::GET		=> 'GET',
			self::HEAD		=> 'HEAD',
			self::POST		=> 'POST',
			self::PUT		=> 'PUT',
			self::DELETE	=> 'DELETE',
			self::TRACE 	=> 'TRACE',
			self::CONNECT 	=> 'CONNECT'
		);
		
		public static function get()
		{
			return new self(self::GET);
		}
		
		public static function post()
		{
			return new self(self::POST);
		}
	}
?>