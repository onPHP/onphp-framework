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
	 * @deprecated 
	**/
	final class LogicalExpression implements LogicalObject
	{
		private $left	= null;
		private $right	= null;
		private $logic	= null;
		
		public function __construct($left, $right, $logic)
		{
			$this->left		= $left;
			$this->right	= $right;
			$this->logic	= $logic;
		}
		
		public function getLeft()
		{
			return $this->left;
		}
		
		public function getRight()
		{
			return $this->right;
		}
		
		public function getLogic()
		{
			return $this->logic;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return 
				'('
				.Expression::toFieldString($this->left, $dialect)
				." {$this->logic} "
				.Expression::toValueString($this->right, $dialect)
				.')';
		}
		
		public function toBoolean(Form $form)
		{
			throw new UnsupportedMethodException(
				"'{$this->logic}' doesn't supported yet"
			);
		}
	}
?>