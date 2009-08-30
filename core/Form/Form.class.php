<?php
/****************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 3 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/

	/**
	 * Complete Form class.
	 * 
	 * @ingroup Form
	 * 
	 * @see http://onphp.org/examples.Form.en.html
	**/
	final class Form extends RegulatedForm
	{
		const WRONG			= 0x0001;
		const MISSING		= 0x0002;
		
		private $errors		= array();
		private $labels		= array();
		
		public static function create()
		{
			return new self;
		}
		
		public function getErrors()
		{
			return array_merge($this->errors, $this->violated);
		}
		
		public function dropAllErrors()
		{
			$this->errors	= array();
			$this->violated	= array();
			
			return $this;
		}
		
		/**
		 * primitive marking
		**/
		
		public function markMissing($primitiveName)
		{
			$prm = $this->get($primitiveName);
			
			$this->errors[$prm->getName()] = Form::MISSING;
			
			return $this;
		}
		
		// rule or primitive
		public function markWrong($name)
		{
			if (
				isset($this->rules[$name])
				|| ($name == $this->get($name)->getName())
			)
				$this->errors[$name] = Form::WRONG;
			
			return $this;
		}
		
		public function markGood($primitiveName)
		{
			$prm = $this->get($primitiveName);
			
			unset($this->errors[$prm->getName()]);
			
			return $this;
		}
		
		/**
		 * Bogus spelling of method's name below.
		 * 
		 * @deprecated and removed during 0.5
		**/
		public function getTexturalErrors()
		{
			return $this->getTextualErrors();
		}
		
		/**
		 * Returns plain list of error's labels
		**/
		public function getTextualErrors()
		{
			$list = array();
			
			foreach ($this->labels as $name => $error)
				if (isset($this->violated[$name], $error[$this->violated[$name]]))
					$list[] = $error[$this->violated[$name]];
				elseif (isset($this->errors[$name], $error[$this->errors[$name]]))
					$list[] = $error[$this->errors[$name]];
			
			return $list;
		}
		
		public function getTextualErrorFor($name)
		{
			if (isset($this->violated[$name], $this->labels[$name][$this->violated[$name]]))
				return $this->labels[$name][$this->violated[$name]];
			elseif (isset($this->errors[$name], $this->labels[$name][$this->errors[$name]]))
				return $this->labels[$name][$this->errors[$name]];
			else
				return null;
		}
		
		public function addWrongLabel($primitiveName, $label)
		{
			return $this->addErrorLabel($primitiveName, Form::WRONG, $label);
		}
		
		public function addMissingLabel($primitiveName, $label)
		{
			return $this->addErrorLabel($primitiveName, Form::MISSING, $label);
		}
		
		public function import(&$scope)
		{
			foreach ($this->primitives as $name => $prm)
				$this->importPrimitive($scope, $prm);
			
			return $this;
		}
		
		public function importMore(&$scope)
		{
			foreach ($this->primitives as $name => $prm)
				if (
					$prm->getValue() === null ||
					($prm instanceof PrimitiveBoolean && !$prm->getValue())
				)
					$this->importPrimitive($scope, $prm);
			
			return $this;
		}
		
		public function importOne($primitiveName, &$scope)
		{
			$this->importPrimitive($scope, $this->get($primitiveName));
			
			return $this;
		}
		
		public function importOneMore($primitiveName, &$scope)
		{
			$prm = $this->get($primitiveName);
			
			if (
				$prm->getValue() === null
				|| ($prm instanceof PrimitiveBoolean && !$prm->getValue())
			)
				$this->importPrimitive($scope, $prm);
			
			return $this;
		}
		
		private function importPrimitive(&$scope, BasePrimitive $prm)
		{
			$name	= $prm->getName();
			$result	= $prm->import($scope);
			
			if (null === $result) {
				if ($prm->isRequired())
					$this->errors[$name] = self::MISSING;
			} elseif (true === $result) {
				unset($this->errors[$name]);
			} else
				$this->errors[$name] = self::WRONG;
			
			/* NOTREACHED */
		}
		
		/**
		 * Assigns specific label for given primitive and error type.
		 * One more example of horrible documentation style.
		 * 
		 * @param	$name		string	primitive or rule name
		 * @param	$errorType	enum	Form::(WRONG|MISSING)
		 * @param	$label		string	YDFB WTF is this :-) (c) /.
		 * @return	$this		Form	itself
		**/
		private function addErrorLabel($name, $errorType, $label)
		{
			if (
				!($errorType == Form::WRONG && isset($this->violated[$name]))
				&& (
					!isset($this->rules[$name])
					&& !$name = $this->get($name)->getName()
				)
			)
				throw new ObjectNotFoundException(
					"knows nothing about '{$name}'"
				);
			
			$this->labels[$name][$errorType] = $label;
			
			return $this;
		}
	}
?>