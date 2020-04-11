<?php
/***************************************************************************
 *   Copyright (C) 2008 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Util\Router;

use OnPHP\Main\Flow\HttpRequest;
use OnPHP\Main\Net\HttpUrl;

final class RouterHostnameRule extends RouterBaseRule
{
	const SCHEME_HTTP			= 'http';
	const SCHEME_HTTPS			= 'https';

	protected $hostVariable		= ':';
	protected $regexDelimiter	= '#';

	protected $scheme			= null;
	protected $defaultRegex		= null;
	protected $route			= null;
	protected $routeProcessed	= false;
	protected $variables		= array();
	protected $parts			= array();
	protected $requirements		= array();
	protected $values			= array();

	protected $staticCount		= 0;

	/**
	 * @return RouterHostnameRule
	**/
	public static function create($route)
	{
		return new self($route);
	}

	public function __construct($route)
	{
		$this->route = trim($route, '.');
		$this->scheme = self::SCHEME_HTTP;
	}

	/**
	 * @return RouterHostnameRule
	**/
	public function setRequirements(array $reqirements)
	{
		$this->requirements = $reqirements;

		return $this;
	}

	public function getRequirements()
	{
		return $this->requirements;
	}

	/**
	 * @return RouterHostnameRule
	**/
	public function setSecure()
	{
		$this->scheme = self::SCHEME_HTTPS;

		return $this;
	}

	public function isSecure()
	{
		return $this->scheme == self::SCHEME_HTTPS;
	}

	/**
	 * @return RouterHostnameRule
	**/
	public function setScheme($schema)
	{
		$this->scheme = $schema;

		return $this;
	}

	public function getScheme()
	{
		return $this->scheme;
	}

	public function match(HttpRequest $request)
	{
		$this->processRoute();

		if (
			$this->isSecureRequest($request)
			&& !$this->isSecure()
		)
			return array();

		if ($request->hasServerVar('HTTP_HOST'))
			$host = $request->getServerVar('HTTP_HOST');
		else
			throw new RouterException('Can not find host');

		$result = array();

		if (preg_match('#:\d+$#', $host, $result) === 1)
			$host = substr($host, 0, -strlen($result[0]));

		$hostStaticCount = 0;
		$values = array();

		$host = trim($host, '.');

		// FIXME: strpos('.', ...), probably?
		if ($host) {
			$host = explode('.', $host);

			foreach ($host as $pos => $hostPart) {
				if (!array_key_exists($pos, $this->parts)) {
					return array();
				}

				$name =
					isset($this->variables[$pos])
						? $this->variables[$pos]
						: null;

				$hostPart = urldecode($hostPart);

				if (
					($name === null)
					&& ($this->parts[$pos] != $hostPart)
				) {
					return array();
				}

				if (
					($this->parts[$pos] !== null)
					&& !preg_match(
						$this->regexDelimiter
						.'^'.$this->parts[$pos].'$'
						.$this->regexDelimiter.'iu',

						$hostPart
					)
				) {
					return array();
				}

				if ($name !== null) {
					$values[$name] = $hostPart;
				} else {
					++$hostStaticCount;
				}
			}
		}

		if ($this->staticCount != $hostStaticCount)
			return array();

		$return = $values + $this->defaults;

		foreach ($this->variables as $var) {
			if (!array_key_exists($var, $return))
				return array();
		}

		$this->values = $values;

		return $return;
	}

	public function assembly(
		array $data = array(),
		$reset = false,
		$encode = false
	)
	{
		$this->processRoute();

		$host = array();
		$flag = false;

		foreach ($this->parts as $key => $part) {
			$name =
				isset($this->variables[$key])
					? $this->variables[$key]
					: null;

			$useDefault = false;

			if (
				isset($name)
				&& array_key_exists($name, $data)
				&& ($data[$name] === null)
			) {
				$useDefault = true;
			}

			if ($name) {
				if (isset($data[$name]) && !$useDefault) {
					$host[$key] = $data[$name];
					unset($data[$name]);
				} elseif (
					!$reset
					&& !$useDefault
					&& isset($this->values[$name])
				) {
					$host[$key] = $this->values[$name];
				} elseif (isset($this->defaults[$name])) {
					$host[$key] = $this->defaults[$name];
				} else {
					// FIXME: bogus message
					throw new RouterException($name . ' is not specified');
				}
			} else {
				$host[$key] = $part;
			}
		}

		$return = null;

		foreach (array_reverse($host, true) as $key => $value) {
			if (
				$flag
				|| !isset($this->variables[$key])
				|| ($value !== $this->getDefault($this->variables[$key]))
			) {
				if ($encode)
					$value = urlencode($value);

				$return = '.'.$value.$return;
				$flag = true;
			}
		}

		// FIXME: rtrim, probably?
		$host = trim($return, '.');

		return $this->resolveSchema() . '://' . $host . $this->resolvePath();

	}

	/**
	 * @return RouterHostnameRule
	**/
	protected function processRoute()
	{
		if ($this->routeProcessed)
			return $this;

		// FIXME: if (strpos('.', ...), probably?
		if ($this->route) {
			foreach (explode('.', $this->route) as $pos => $part) {
				if (substr($part, 0, 1) == $this->hostVariable) {
					$name = substr($part, 1);

					$this->parts[$pos] = (
						isset($this->requirements[$name])
							? $this->requirements[$name]
							: $this->defaultRegex
						);

					$this->variables[$pos] = $name;
				} else {
					$this->parts[$pos] = $part;
					++$this->staticCount;
				}
			}
		}

		$this->routeProcessed = true;

		return $this;
	}

	protected function isSecureRequest(HttpRequest $request)
	{
		return (
			$request->hasServerVar('HTTPS')
			&& $request->getServerVar('HTTPS') == 'on'
		) || (
			$request->hasServerVar('SERVER_PORT')
			&& (int) $request->getServerVar('SERVER_PORT') === 443
		);
	}

	protected function resolveSchema()
	{
		$base = RouterRewrite::me()->getBaseUrl();

		if ($this->scheme) {
			return $this->scheme;
		} elseif (
			($base instanceof HttpUrl)
			&& $base->getScheme()
		) {
			return $base->getScheme();
		} else {
			throw new RouterException('Cannot resolve scheme');
		}
	}

	protected function resolvePath()
	{
		if (
			($base = RouterRewrite::me()->getBaseUrl())
			&& ($this->scheme == $base->getScheme())
		) {
			return rtrim($base->getPath(), '/');
		} else {
			return '';
		}
	}
}
?>
