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
			return $this->markCustom($primitiveName, Form::MISSING);
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
		 * Set's custom error mark for primitive.
		**/
		public function markCustom($primitiveName, $customMark)
		{
			Assert::isInteger($customMark);
			
			$this->errors[$this->get($primitiveName)->getName()] = $customMark;
			
			return $this;
		}
		
		/**
		 * Returns plain list of error's labels
		**/
		public function getTextualErrors()
		{
			$list = array();
			
			foreach ($this->labels as $name => $error) {
				if ($label = $this->getTextualErrorFor($name))
					$list[] = $label;
			}
			
			return $list;
		}
		
		public function getTextualErrorFor($name)
		{
			if (
				isset(
					$this->violated[$name],
					$this->labels[$name][$this->violated[$name]]
				)
			)
				return $this->labels[$name][$this->violated[$name]];
			elseif (
				isset(
					$this->errors[$name],
					$this->labels[$name][$this->errors[$name]]
				)
			)
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
		
		public function addCustomLabel($primitiveName, $customMark, $label)
		{
			return $this->addErrorLabel($primitiveName, $customMark, $label);
		}
		
		public function import($scope)
		{
			foreach ($this->primitives as $name => $prm)
				$this->importPrimitive($scope, $prm);
			
			return $this;
		}
		
		public function importMore($scope)
		{
			foreach ($this->primitives as $name => $prm) {
				if (
					$prm->getValue() === null ||
					($prm instanceof PrimitiveBoolean && !$prm->getValue())
				)
					$this->importPrimitive($scope, $prm);
			}
			
			return $this;
		}
		
		public function importOne($primitiveName, $scope)
		{
			return $this->importPrimitive($scope, $this->get($primitiveName));
		}
		
		public function importValue($primitiveName, $value)
		{
			$prm = $this->get($primitiveName);
			
			return $this->checkImportResult($prm, $prm->importValue($value));
		}
		
		public function importOneMore($primitiveName, $scope)
		{
			$prm = $this->get($primitiveName);
			
			if (
				$prm->getValue() === null
				|| ($prm instanceof PrimitiveBoolean && !$prm->getValue())
			)
				return $this->importPrimitive($scope, $prm);
			
			return $this;
		}
		
		private function importPrimitive($scope, BasePrimitive $prm)
		{
			return $this->checkImportResult($prm, $prm->import($scope));
		}
		
		private function checkImportResult(BasePrimitive $prm, $result)
		{
			$name = $prm->getName();
			
			if (null === $result) {
				if ($prm->isRequired())
					$this->errors[$name] = self::MISSING;
			} elseif (true === $result) {
				unset($this->errors[$name]);
			} else
				$this->errors[$name] = self::WRONG;
			
			return $this;
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
				throw new MissingElementException(
					"knows nothing about '{$name}'"
				);
			
			$this->labels[$name][$errorType] = $label;
			
			return $this;
		}
	}
?>