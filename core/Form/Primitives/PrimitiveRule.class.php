<?php
/***************************************************************************
 *   Copyright (C) 2008 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveRule extends BasePrimitive
	{
		/** @var Form */
		private $form		= null;

		/** @var BinaryExpression */
		private $expression	= null;

		/**
		 * @return PrimitiveRule
		**/
		public function setForm(Form $form)
		{
			$this->form = $form;

			return $this;
		}

		/**
		 * @return PrimitiveRule
		**/
		public function setExpression(LogicalObject $exp)
		{
			$this->expression = $exp;

			return $this;
		}

		public function import($scope)
		{
			Assert::isNotNull($this->form);
			Assert::isNotNull($this->expression);

			return $this->expression->toBoolean($this->form);
		}
	}
?>