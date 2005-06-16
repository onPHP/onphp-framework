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

	class FullTextSearch implements LogicalObject
	{
		private $logic = null;
		private $field = null;
		private $words = null;

		public function __construct($field, $words, $logic)
		{
			Assert::isString($field);
			Assert::isArray($words);
			
			$this->field = $field;
			$this->words = $words;
			$this->logic = $logic;
		}

		public function toString(DB $db)
		{
			return
				$db->fullTextSearch(
					$this->field, 
					$this->words, 
					$this->logic
				);
		}

		public function toBoolean(Form $form)
		{
			throw new UnsupportedMethodException();
		}
	}
?>