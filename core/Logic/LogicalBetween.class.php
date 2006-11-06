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
	class LogicalBetween extends DualTransformableExpression
	{
		protected $left   = null;
		protected $right  = null;
		
		private $field  = null;
		
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
		
		public function applyMapping(StorableDAO $dao)
		{
			$this->field = $this->transformProperty($dao, $this->field);
			
			return parent::applyMapping($dao);
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