<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup OSQL
	**/
	final class DropTableQuery extends QueryIdentification
	{
		private $name		= null;
		
		private $cascade	= false;
		
		public function getId()
		{
			throw new UnsupportedMethodException();
		}
		
		public function __construct($name, $cascade = false)
		{
			$this->name = $name;
			$this->cascade = (true === $cascade);
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return
				'DROP TABLE '.$dialect->quoteTable($this->name)
				.$dialect->dropTableMode($this->cascade)
				.';';
		}
	}
?>