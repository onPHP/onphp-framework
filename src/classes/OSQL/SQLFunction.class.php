<?php
/***************************************************************************
 *   Copyright (C) 2005 by Anton Lebedevich                                *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class SQLFunction extends FieldTable
	{
		private $function	= null;
		private $alias		= null;

		public function __construct(DBField $field, $function, $alias)
		{
			parent::__construct($field);

			$this->function	= $function;
			$this->alias	= $alias;
		}

		public function toString(Dialect $dialect)
		{
			return
				$this->function.'('.parent::toString($dialect).')'.
				($this->alias ? ' AS '.$dialect->quoteField($this->alias) : '');
		}
	}
?>