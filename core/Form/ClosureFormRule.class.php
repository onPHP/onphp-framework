<?php
/***************************************************************************
 *   Copyright (C) 2012 by Timofey A. Anisimov                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Form
	 */
	final class ClosureFormRule implements LogicalObject
	{
		private $closure = null;

		/**
		 * @static
		 * @param Closure $closure
		 * @return ClosureFormRule
		 */
		public static function create(Closure $closure)
		{
			return new self($closure);
		}

		public function __construct(Closure $closure)
		{
			$this->closure = $closure;
		}

		/**
		 * @param Form $form
		 * @return boolean
		 */
		public function toBoolean(Form $form)
		{
			$func = $this->closure;

			return $func($form);
		}

		/**
		 * @param Dialect $dialect
		 * @throws UnsupportedMethodException
		 */
		public function toDialectString(Dialect $dialect)
		{
			throw new UnsupportedMethodException('No dialect for form rule.');
		}
	}
