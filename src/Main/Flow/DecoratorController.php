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

namespace OnPHP\Main\Flow;

/**
 * @ingroup Flow
**/
abstract class DecoratorController implements Controller
{
	protected $inner = null;

	public function __construct(Controller $inner)
	{
		$this->inner = $inner;
	}

	/**
	 * @return ModelAndView
	**/
	public function handleRequest(HttpRequest $request)
	{
		return $this->inner->handleRequest($request);
	}
}
?>