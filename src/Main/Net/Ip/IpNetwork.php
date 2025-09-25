<?php
/***************************************************************************
 *   Copyright (C) 2007 by Vladimir A. Altuchov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Net\Ip;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Main\Base\SingleRange;

/**
 * @ingroup Ip
 * @deprecated use IpRange instead
**/
final class IpNetwork implements SingleRange
{
	const MASK_MAX_SIZE = 31;

	private $ip			= null;
	private $end 		= null;
	private $mask 		= null;
	private $longMask 	= null;

	/**
	 * @return IpNetwork
	**/
	public static function create(IpAddress $ip, $mask)
	{
		return new self($ip, $mask);
	}

	public function __construct(IpAddress $ip, $mask)
	{
		Assert::isInteger($mask);

		if ($mask == 0 || self::MASK_MAX_SIZE < $mask)
			throw new WrongArgumentException('wrong mask given');

		$this->longMask =
			(int) (pow(2, (32 - $mask)) * (pow(2, $mask) - 1));

		if (($ip->getLongIp() & $this->longMask) != $ip->getLongIp())
			throw new WrongArgumentException('wrong ip network given');

		$this->ip = $ip;
		$this->mask = $mask;
	}

	public function getMask()
	{
		return $this->mask;
	}

	/**
	 * @return IpAddress
	**/
	public function getStart(): ?IpAddress
	{
		return $this->ip;
	}

	/**
	 * @return IpAddress
	**/
	public function getEnd(): ?IpAddress
	{
		if (!$this->end) {
			$this->end =
				IpAddress::create(
					long2ip($this->ip->getLongIp() | ~$this->longMask)
				);
		}

		return $this->end;
	}

	/**
	 * @param IpAddress $probe
	 * @return bool
	 * @throws WrongArgumentException
	 */
	public function contains(object $probe): bool
	{
		Assert::isInstance($probe, IpAddress::class);

		return
			($probe->getLongIp() & $this->longMask)
			== $this->ip->getLongIp();
	}
}
?>