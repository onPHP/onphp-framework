<?php
/****************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * SQL's BETWEEN or logical check whether value in-between given limits.
	 * 
	 * @ingroup Logic
	**/
	final class LogicalBetween implements LogicalObject, MappableObject
	{
		private $field  = null;
		private $left   = null;
		private $right  = null;
		
		public function __construct($field, $left, $right)
		{
			$this->left		= $left;
			$this->right	= $right;
			$this->field	= $field;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return
				'('
				.$dialect->toFieldString($this->field)
				.' BETWEEN '
				.$dialect->toValueString($this->left)
				.' AND '
				.$dialect->toValueString($this->right)
				.')';
		}
		
		/**
		 * @return LogicalBetween
		**/
		public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
		{
			return new self(
				$dao->guessAtom($this->field, $query),
				$dao->guessAtom($this->left, $query),
				$dao->guessAtom($this->right, $query)
			);
		}
		
		public function toBoolean(Form $form)
		{
			$left	= $form->toFormValue($this->left);
			$right	= $form->toFormValue($this->right);
			$value	= $form->toFormValue($this->field);
			
			return ($left	<= $value)
				&& ($value	<= $right);
		}
	}
?>