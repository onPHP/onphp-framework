<?php
/****************************************************************************
 *   Copyright (C) 2008 by Dmitry V. Sokolov, Denis M. Gabaidulin           *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

namespace OnPHP\Main\Flow;

use OnPHP\Main\Base\RequestType;
use OnPHP\Core\Base\Assert;

// TODO: add action => requestType mapper
/**
 * @ingroup Flow
**/
final class ProxyController implements Controller
{
	private $innerController	= null;
	private $request			= null;
	private $requestType		= null;
	private $requestGetter		= null;

	private static $requestGetterMap = array(
		RequestType::ATTACHED	=> 'Attached',
		RequestType::GET		=> 'Get',
		RequestType::POST		=> 'Post'
	);

	/**
	 * @return ProxyController
	**/
	public static function create()
	{
		return new self;
	}

	public function __construct()
	{
		$this->requestType = RequestType::post();
	}

	/**
	 * @return ProxyController
	**/
	public function setInner(Controller $controller)
	{
		$this->innerController = $controller;

		return $this;
	}

	/**
	 * @return Controller
	**/
	public function getInner()
	{
		return $this->innerController;
	}

	public function getName()
	{
		return get_class($this->innerController);
	}

	/**
	 * @return ModelAndView
	**/
	public function handleRequest(HttpRequest $request)
	{
		return $this->getInner()->handleRequest($request);
	}

	/**
	 * @return ProxyController
	**/
	public function setRequestType(RequestType $requestType)
	{
		$this->requestType = $requestType;

		return $this;
	}

	public function isActive($request)
	{
		return $this->fetchVariable('controller', $request)
			? (
				$this->fetchVariable('controller', $request)
				== get_class($this->getInner())
			)
			: false;
	}

	public function getRequestGetter()
	{
		Assert::isNotNull($this->requestType);

		if (!$this->requestGetter)
			$this->requestGetter =
				self::$requestGetterMap[$this->requestType->getId()];

		return $this->requestGetter;
	}

	private function fetchVariable($name, HttpRequest $request)
	{
		return $request->{'has'.$this->getRequestGetter().'Var'}($name)
			? $request->{'get'.$this->getRequestGetter().'Var'}($name)
			: false;
	}
}
?>