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
	final class EqualsLowerExpression implements LogicalObject
	{
		private $left	= null;
		private $right	= null;
		
		public function __construct($left, $right)
		{
			$this->left		= $left;
			$this->right	= $right;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return 
				'('
				.Expression::toFieldString(
					SQLFunction::create('lower', $this->left), 
					$dialect
				)
				." = "
				.Expression::toValueString(
					SQLFunction::create('lower', $this->right),
					$dialect
				)
				.')';
		}
		
		public function toBoolean(Form $form)
		{
			$left	= Expression::toFormValue($form, $this->left);
			$right	= Expression::toFormValue($form, $this->right);
			
			$both = 
				(null !== $left)
				&& (null !== $right);
				
			return $both && (mb_strtolower($left) === mb_strtolower($right));
		}
	}
?>