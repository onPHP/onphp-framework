<?php
/****************************************************************************
 *   Copyright (C) 2008 by Vladlen Y. Koshelev                              *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

namespace OnPHP\Main\OQL\Expression;

/**
 * @ingroup OQL
**/
class OqlQueryExpression extends OqlQueryParameter
{
	private static $classes = array();

	private $className	= null;
	private $parameters	= array();

	/**
	 * @return OqlQueryExpression
	**/
	public static function create()
	{
		return new self;
	}

	public function getClassName()
	{
		return $this->className;
	}

	/**
	 * @return OqlQueryExpression
	**/
	public function setClassName($className)
	{
		$this->className = $className;

		return $this;
	}

	public function getParameters()
	{
		return $this->parameters;
	}

	public function hasParameter($index)
	{
		return isset($this->parameters[$index]);
	}

	/**
	 * @return OqlQueryParameter
	**/
	public function getParameter($index)
	{
		return $this->parameters[$index];
	}

	/**
	 * @return OqlQueryExpression
	**/
	public function addParameter(OqlQueryParameter $parameter)
	{
		$this->parameters[] = $parameter;

		return $this;
	}

	/**
	 * @return OqlQueryExpression
	**/
	public function setParameter($index, OqlQueryParameter $parameter)
	{
		$this->parameters[$index] = $parameter;

		return $this;
	}

	public function evaluate($values)
	{
		$className = $this->getClassName();

		if (!isset(self::$classes[$className]))
			self::$classes[$className] = new \ReflectionClass($className);

		$class = self::$classes[$className];
		$parametersCount = count($class->getConstructor()->getParameters());
		$parameters = array();

		for ($i = 0; $i < $parametersCount; $i++) {
			if (!$this->hasParameter($i))
				break;

			$parameters[$i] = $this->getParameter($i)->evaluate($values);
		}

		return $class->newInstanceArgs($parameters);
	}
}
?>