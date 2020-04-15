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

namespace OnPHP\Main\Net\Http;

use OnPHP\Core\Base\Enumeration;
use OnPHP\Core\Exception\WrongArgumentException;

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
	const PROPFIND	= 9;
	const PROPPATCH	= 10;
	const MKCOL 	= 11;
	const COPY		= 12;
	const MOVE		= 13;
	const LOCK		= 14;
	const UNLOCK	= 15;

	protected $names = array(
		self::OPTIONS 	=> 'OPTIONS',
		self::GET		=> 'GET',
		self::HEAD		=> 'HEAD',
		self::POST		=> 'POST',
		self::PUT		=> 'PUT',
		self::DELETE	=> 'DELETE',
		self::TRACE 	=> 'TRACE',
		self::CONNECT 	=> 'CONNECT',
		self::PROPFIND	=> 'PROPFIND',
		self::PROPPATCH => 'PROPPATCH',
		self::MKCOL 	=> 'MKCOL',
		self::COPY		=> 'COPY',
		self::MOVE		=> 'MOVE',
		self::LOCK		=> 'LOCK',
		self::UNLOCK 	=> 'UNLOCK',
	);

	public static function get()
	{
		return new self(self::GET);
	}

	public static function post()
	{
		return new self(self::POST);
	}

	/**
	 * @return HttpMethod
	 */
	public static function any()
	{
		return self::get();
	}

	public static function createByName($name)
	{
		$key = array_search($name, self::any()->getNameList());

		if ($key === false)
			throw new WrongArgumentException();

		return new self($key);
	}
}
?>