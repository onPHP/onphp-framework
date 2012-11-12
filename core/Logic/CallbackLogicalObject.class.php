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

	/**
	 * Wrapper around given childs of LogicalObject with custom logic-glue's.
	 *
	 * @ingroup Logic
	**/
	namespace Onphp;

	class CallbackLogicalObject implements LogicalObject
	{
		/**
		 * @var \Closure
		 */
		private $callback = null;

		/**
		 * @static
		 * @param \Closure $callback
		 * @return \Onphp\CallbackLogicalObject
		 */
		static public function create($callback)
		{
			return new static($callback);
		}

		/**
		 * @param \Closure $callback
		 */
		public function __construct($callback)
		{
			Assert::isTrue(is_callable($callback, true), 'callback must be callable');
			$this->callback = $callback;
		}

		/**
		 * @param \Onphp\Form $\Onphp\Form
		 * @return bool
		 */
		public function toBoolean(Form $form)
		{
			return call_user_func($this->callback, $form);
		}

		/**
		 * @param \Onphp\Dialect $\Onphp\Dialect
		 * @throws \Onphp\UnimplementedFeatureException
		 */
		public function toDialectString(Dialect $dialect)
		{
			throw new UnimplementedFeatureException("toDialectString is not needed here");
		}
	}
?>