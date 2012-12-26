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
		/**
		 * @var LogicalObject
		 */
		private $expression	= null;

		public function __clone()
		{
			$this->expression = clone $this->expression;
		}

		/**
		 * @return PrimitiveRule
		**/
		public function setExpression(LogicalObject $exp)
		{
			$this->expression = $exp;

			return $this;
		}

		public function import($scope, Form $form = null)
		{
			Assert::isNotNull($form, 'expects Form as 2-nd argument');
			Assert::isNotNull($this->expression, 'setExpression first');

			return $this->expression->toBoolean($form) === true;
		}
	}
?>