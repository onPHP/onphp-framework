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
	 * @ingroup Logic
	**/
	final class EqualsLowerExpression extends DualTransformableExpression
	{
		public function __construct($left, $right)
		{
			$this->left		= $left;
			$this->right	= $right;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return 
				'('
				.$dialect->toFieldString(
					SQLFunction::create('lower', $this->left)
				)
				." = "
				.$dialect->toValueString(
					SQLFunction::create('lower', $this->right)
				)
				.')';
		}
		
		public function toBoolean(Form $form)
		{
			$left	= $form->toFormValue($this->left);
			$right	= $form->toFormValue($this->right);
			
			$both = 
				(null !== $left)
				&& (null !== $right);
				
			return $both && (mb_strtolower($left) === mb_strtolower($right));
		}
	}
?>