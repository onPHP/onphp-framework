<?php
/****************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich, Konstantin V. Arkhipov *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * Base for all full-text stuff.
	 * 
	 * @ingroup OSQL
	 * @ingroup Module
	**/
	abstract class FullText
		implements DialectString, MappableObject, LogicalObject
	{
		protected $logic = null;
		protected $field = null;
		protected $words = null;
		
		public function __construct($field, $words, $logic)
		{
			if (is_string($field))
				$field = new DBField($field);
			
			Assert::isArray($words);
			
			$this->field = $field;
			$this->words = $words;
			$this->logic = $logic;
		}
		
		/**
		 * @return FullText
		**/
		public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
		{
			return new $this(
				$dao->guessAtom(
					DBField::create($this->field->getField(), $dao->getTable()),
					$query
				),
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