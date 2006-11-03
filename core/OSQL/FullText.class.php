<?php
/***************************************************************************
 *   Copyright (C) 2005 by Anton E. Lebedevich, Konstantin V. Arkhipov     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Base for all full-text stuff.
	 * 
	 * @ingroup OSQL
	**/
	abstract class FullText implements DialectString
	{
		protected $logic = null;
		protected $field = null;
		protected $words = null;
		
		public function __construct($field, $words, $logic)
		{
			Assert::isTrue(
				is_string($field) ||
				$field instanceof DBField
			);
			Assert::isArray($words);
			
			$this->field = $field;
			$this->words = $words;
			$this->logic = $logic;
		}
	}
?>