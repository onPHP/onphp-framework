<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Vladimir A. Altuchov                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Net\Ip;

use OnPHP\Core\DB\Dialect;
use OnPHP\Core\OSQL\DialectString;
use OnPHP\Core\Base\Stringable;
use OnPHP\Main\Util\TypesUtils;
use OnPHP\Core\Exception\WrongArgumentException;

/**
 * @ingroup Ip
**/
class IpAddress implements Stringable, DialectString
{
	private $longIp = null;

	/**
	 * @return IpAddress
	**/
	public static function create($ip)
	{
		return new self($ip);
	}

	public static function createFromCutted($ip)
	{
		if (substr_count($ip, '.') < 3)
			return self::createFromCutted($ip.'.0');

		return self::create($ip);
	}

	public function __construct($ip)
	{
		$this->setIp($ip);
	}

	/**
	 * @return IpAddress
	**/
	public function setIp($ip)
	{
		$long = ip2long($ip);

		if ($long === false)
			throw new WrongArgumentException('wrong ip given');

		$this->longIp = $long;

		return $this;
	}

	public function getLongIp()
	{
		return $this->longIp;
	}

	public function toString()
	{
		return long2ip($this->longIp);
	}

	public function toDialectString(Dialect $dialect)
	{
		return $dialect->quoteValue($this->toString());
	}

	public function toSignedInt()
	{
		return TypesUtils::unsignedToSigned($this->longIp);
	}
}
?>
