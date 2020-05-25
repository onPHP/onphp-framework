<?php
/***************************************************************************
 *   Copyright (C) 2008 by Evgeny V. Kokovikhin                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Net\Http;

use OnPHP\Core\Base\Assert;
use OnPHP\Main\Base\CollectionItem;
use OnPHP\Core\Exception\WrongStateException;

/*$id$*/

/**
 * @ingroup Http
**/
final class Cookie extends CollectionItem
{
	private $name		= null;
	private $value		= null;
	private $expire 	= 0;
	private $path		= null;
	private $domain		= null;
	private $secure		= false;
	private $httpOnly	= false;
	private $sameSite	= 'Lax';

	/**
	 * @return Cookie
	**/
	public static function create($name)
	{
		return new self($name);
	}

	public function __construct($name)
	{
		$this->id = $this->name = $name;
	}

	/**
	 * @return string|null
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param   mixed   $value
	 * @return  Cookie
	 */
	public function setValue($value)
	{
		$this->value = $value;

		return $this;
	}

	/**
	 * @return mixed|null
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param   int	    $expire
	 * @return  Cookie
	 */
	public function setMaxAge(int $expire)
	{
		$this->expire = $expire;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getMaxAge()
	{
		return $this->expire;
	}

	/**
	 * @param   string  $path
	 * @return  Cookie
	 */
	public function setPath(string $path = null)
	{
		$this->path = $path;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * @param   string  $domain
	 * @return  Cookie
	 */
	public function setDomain(string $domain = null)
	{
		$this->domain = $domain;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getDomain()
	{
		return $this->domain;
	}

	/**
	 * @param   bool    $secure
	 * @return  Cookie
	 */
	public function setSecure(bool $secure = true)
	{
		$this->secure = $secure;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function getSecure()
	{
		return $this->secure;
	}

	/**
	 * @param   bool    $httpOnly
	 * @return  Cookie
	 */
	public function setHttpOnly(bool $httpOnly = true)
	{
		$this->httpOnly = $httpOnly;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function getHttpOnly()
	{
		return $this->httpOnly;
	}

	/**
	 * @return Cookie
	 */
	public function setSameSiteStrict()
	{
		$this->sameSite = 'Strict';

		return $this;
	}

	/**
	 * @return Cookie
	 */
	public function setSameSiteLax()
	{
		$this->sameSite = 'Lax';

		return $this;
	}

	/**
	 * @return Cookie
	 */
	public function setSameSiteNone()
	{
		$this->sameSite = 'None';

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSameSite()
	{
		return $this->sameSite;
	}

	/**
	 * @return bool
	 * @throws WrongStateException
	 */
	public function httpSet()
	{
		if (headers_sent())
			throw new WrongStateException('headers already send');

		$options = array(
			'expires'   => $this->getMaxAge() === 0 ? 0 : $this->getMaxAge() + time(),
			'secure'    => $this->getSecure(),
			'httponly'  => $this->getHttpOnly(),
			'samesite'  => $this->getSameSite()
		);
		if ($this->getPath()) {
			$options['path'] = $this->getPath();
		}
		if ($this->getDomain()) {
			$options['domain'] = $this->getDomain();
		}

		return
			setcookie(
				$this->getName(),
				$this->getValue(),
				$options
			);
	}
}
?>