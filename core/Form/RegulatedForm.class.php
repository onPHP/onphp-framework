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
			return $this->add(
				Primitive::rule($name)
					->setExpression($rule)
					->setForm($this)
			);
		}
		
		/**
		 * @throws MissingElementException
		 * @return Form
		**/
		public function dropRuleByName($name)
		{
			$rule = $this->get($name);

			Assert::isInstance($rule, 'PrimitiveRule',
				'primitive by "'.$name.'" must be instanceof PrimitiveRule! gived, "'.get_class($rule).'"'
			);

			return $this->drop($name);
		}
		
		public function ruleExists($name)
		{
			return ($this->exists($name) && ($this->get($name) instanceof PrimitiveRule));
		}
		
		/**
		 * @return Form
		**/
		public function checkRules()
		{
			$primitives = $this->getPrimitiveList();
			foreach($primitives as $prm)
			{
				if($prm instanceof PrimitiveRule)
					if (!$prm->import(null))
						$prm->markWrong();
			}
			
			return $this;
		}
	}
?>