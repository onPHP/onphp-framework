<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Rules support for final Form.
	 * 
	 * @ingroup Form
	 * @ingroup Module
	**/
	abstract class RegulatedForm extends PlainForm
	{
		/**
		 * @throws WrongArgumentException
		 * @return Form
		**/
		public function addRule($name, LogicalObject $rule)
		{
			Assert::isString($name);
			
			return $this->add(
				Primitive::rule($name)->setExpression($rule)
			);
		}
		
		/**
		 * @throws MissingElementException
		 * @return Form
		**/
		public function dropRuleByName($name)
		{
			$rule = $this->get($name);
			if (!$rule instanceof PrimitiveRule) {
				throw new MissingElementException("no such PrimitiveRule with '{$name}' name");
			}
			return $this->drop($name);
		}
		
		public function ruleExists($name)
		{
			return $this->primitiveExists($name)
				&& $this->get($name) instanceof PrimitiveRule;
		}
	}
?>