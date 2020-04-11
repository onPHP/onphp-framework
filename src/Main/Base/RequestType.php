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

namespace OnPHP\Main\Base;

use OnPHP\Core\Base\Enumeration;
use OnPHP\Core\Base\Assert;

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
	public function setId($id)
	{
		Assert::isNull($this->id, 'i am immutable one!');

		return parent::setId($id);
	}

	/**
	 * @return RequestType
	**/
	public static function get()
	{
		return self::getInstance(self::GET);
	}

	/**
	 * @return RequestType
	**/
	public static function post()
	{
		return self::getInstance(self::POST);
	}

	/**
	 * @return RequestType
	**/
	public static function files()
	{
		return self::getInstance(self::FILES);
	}

	/**
	 * @return RequestType
	**/
	public static function cookie()
	{
		return self::getInstance(self::COOKIE);
	}

	/**
	 * @return RequestType
	**/
	public static function session()
	{
		return self::getInstance(self::SESSION);
	}

	/**
	 * @return RequestType
	**/
	public static function attached()
	{
		return self::getInstance(self::ATTACHED);
	}

	/**
	 * @return RequestType
	**/
	public static function server()
	{
		return self::getInstance(self::SERVER);
	}

	/**
	 * @return RequestType
	**/
	private static function getInstance($id)
	{
		static $instances = array();

		if (!isset($instances[$id]))
			$instances[$id] = new self($id);

		return $instances[$id];
	}
}
?>