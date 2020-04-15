<?php
/****************************************************************************
 *   Copyright (C) 2011 Victor V. Bolshov                                   *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

namespace OnPHP\Core\Logic;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Form\Form;
use OnPHP\Core\DB\Dialect;
use OnPHP\Core\Exception\UnimplementedFeatureException;

/**
 * Wrapper around given childs of LogicalObject with custom logic-glue's.
 *
 * @ingroup Logic
**/
class CallbackLogicalObject implements LogicalObject
{
	/**
	 * @var callable
	 */
	private $callback = null;

	/**
	 * @static
	 * @param callable $callback
	 * @return CallbackLogicalObject
	 */
	static public function create($callback)
	{
		return new static($callback);
	}

	/**
	 * @param callable $callback
	 */
	public function __construct($callback)
	{
		Assert::isTrue(is_callable($callback, true), 'callback must be callable');
		$this->callback = $callback;
	}

	/**
	 * @param Form $form
	 * @return bool
	 */
	public function toBoolean(Form $form)
	{
		return call_user_func($this->callback, $form);
	}

	/**
	 * @param Dialect $dialect
	 * @throws UnimplementedFeatureException
	 */
	public function toDialectString(Dialect $dialect)
	{
		throw new UnimplementedFeatureException("toDialectString is not needed here");
	}
}
?>