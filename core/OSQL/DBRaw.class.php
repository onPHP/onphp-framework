<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Karma's destroyer.
	 *
	 * @ingroup OSQL
	**/
	final class DBRaw implements LogicalObject
	{
		private $string = null;

		/**
		 * @return DBRaw
		**/
		public static function create($value)
		{
			return new self($value);
		}

		public function __construct($rawString)
		{
			$this->string = $rawString;
		}

		public function toDialectString(Dialect $dialect)
		{
			return $this->string;
		}

		public function toBoolean(Form $form)
		{
			throw new UnsupportedMethodException();
		}
	}
?>