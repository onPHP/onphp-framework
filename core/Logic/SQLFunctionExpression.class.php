<?php
/***************************************************************************
 *   Copyright (C) 2014 by Alexey V. Gorbylev                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Allow to use SQL functions in expressions
	 *
	 * @ingroup Logic
	 **/
	class SQLFunctionExpression implements LogicalObject {

		private $arguments = null;

		/**
		 * @throws WrongArgumentException
		 * @return SQLFunctionExpression
		**/
		public static function create()
		{
			$arguments = func_get_args();
			if( count($arguments) == 0 ) {
				throw new WrongArgumentException('You need to pass function name');
			}
			$name = array_shift($arguments);
			Assert::isString($name);
			array_unshift($arguments, $name);
			return new self($arguments);
		}

		protected function __construct($arguments)
		{
			$this->arguments = $arguments;
		}

		public function toDialectString(Dialect $dialect)
		{
			/** @var SQLFunction $sql */
			$sql = call_user_func_array('SQLFunction::create', $this->arguments);
			return $sql->toDialectString( $dialect );
		}

		public function toBoolean(Form $form)
		{
			throw new UnsupportedMethodException();
		}

	}