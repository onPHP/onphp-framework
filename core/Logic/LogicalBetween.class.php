<?php
/****************************************************************************
 *   Copyright (C) 2004-2006 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * SQL's BETWEEN or logical check whether value in-between given limits.
	 * 
	 * @ingroup Logic
	**/
	class LogicalBetween implements LogicalObject
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
				.Expression::toFieldString($this->field, $dialect)
				.' BETWEEN '
				.Expression::toValueString($this->left, $dialect)
				.' AND '
				.Expression::toValueString($this->right, $dialect)
				.')';
		}
		
		public function toBoolean(Form $form)
		{
			$left	= Expression::toFormValue($form, $this->left);
			$right	= Expression::toFormValue($form, $this->right);
			$value	= Expression::toFormValue($form, $this->field);
			
			return ($left	<= $value)
				&& ($value	<= $right);
		}
	}
?>