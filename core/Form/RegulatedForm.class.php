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
		protected $rules		= array(); // forever
		protected $violated		= array(); // rules
		
		/**
		 * @throws WrongArgumentException
		 * @return Form
		**/
		public function addRule($name, LogicalObject $rule)
		{
			Assert::isString($name);
			
			$this->rules[$name] = $rule;
			
			return $this;
		}
		
		/**
		 * @throws MissingElementException
		 * @return Form
		**/
		public function dropRuleByName($name)
		{
			if (isset($this->rules[$name])) {
				unset($this->rules[$name]);
				return $this;
			}
			
			throw new MissingElementException(
				"no such rule with '{$name}' name"
			);
		}
		
		public function ruleExists($name)
		{
			return isset($this->rules[$name]);
		}
		
		/**
		 * @return Form
		**/
		public function checkRules()
		{
			foreach ($this->rules as $name => $logicalObject) {
				if (!$logicalObject->toBoolean($this))
					$this->violated[$name] = Form::WRONG;
			}
			
			return $this;
		}
	}
