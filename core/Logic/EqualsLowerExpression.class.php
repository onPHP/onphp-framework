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
	 * @ingroup Logic
	**/
	final class EqualsLowerExpression implements LogicalObject, MappableObject
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
				.$dialect->toFieldString(
					new SQLFunction('lower', $this->left)
				).' = '
				.$dialect->toValueString(
					is_string($this->right)
						? mb_strtolower($this->right)
						: new SQLFunction('lower', $this->right)
				)
				.')';
		}
		
		/**
		 * @return EqualsLowerExpression
		**/
		public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
		{
			return new self(
				$dao->guessAtom($this->left, $query),
				$dao->guessAtom($this->right, $query)
			);
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