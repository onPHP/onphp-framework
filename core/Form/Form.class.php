<?php
/****************************************************************************
 *   Copyright (C) 2004-2009 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * Complete Form class.
	 *
	 * @ingroup Form
	 * @ingroup Module
	 *
	 * @see http://onphp.org/examples.Form.en.html
	**/
	final class Form extends RegulatedForm
	{
		const WRONG			= 0x0001;
		const MISSING		= 0x0002;

		private $errors				= array();
		private $labels				= array();
		private $describedLabels	= array();

		private $proto				= null;

		private $importFiltering	= true;

		/**
		 * @return Form
		**/
		public static function create()
		{
			return new self;
		}

		public function getErrors()
		{
			return array_merge($this->errors, $this->violated);
		}

		public function check() {
			if ($this->getErrors()) {
				throw new FormValidationException($this);
			}
			return $this;
		}
		
		public function hasError($name)
		{
			return array_key_exists($name, $this->errors)
				|| array_key_exists($name, $this->violated);
		}

		public function getError($name)
		{
			if (array_key_exists($name, $this->errors)) {
				return $this->errors[$name];
			} elseif (array_key_exists($name, $this->violated)) {
				return $this->violated[$name];
			}
			return null;
		}

		public function getInnerErrors()
		{
			$result = $this->getErrors();

			foreach ($this->primitives as $name => $prm) {
				if (
					(
						($prm instanceof PrimitiveFormsList)
						|| ($prm instanceof PrimitiveForm)
					)
					&& $prm->getValue()
				) {
					if ($errors = $prm->getInnerErrors()) {
						$result[$name] = $errors;
					} else {
						unset($result[$name]);
					}
				}
			}

			return $result;
		}

		/**
		 * @return Form
		**/
		public function dropAllErrors()
		{
			$this->errors	= array();
			$this->violated	= array();

			return $this;
		}

		/**
		 * @return Form
		**/
		public function enableImportFiltering()
		{
			$this->importFiltering = true;

			return $this;
		}

		/**
		 * @return Form
		**/
		public function disableImportFiltering()
		{
			$this->importFiltering = false;

			return $this;
		}

		/**
		 * primitive marking
		**/
		//@{
		/**
		 * @return Form
		**/
		public function markMissing($primitiveName)
		{
			return $this->markCustom($primitiveName, Form::MISSING);
		}

		/**
		 * rule or primitive
		 *
		 * @return Form
		**/
		public function markWrong($name)
		{
			if (isset($this->primitives[$name]))
				$this->errors[$name] = self::WRONG;
			elseif (isset($this->rules[$name]))
				$this->violated[$name] = self::WRONG;
			else
				throw new MissingElementException(
					$name.' does not match known primitives or rules'
				);

			return $this;
		}

		/**
		 * @return Form
		**/
		public function markGood($primitiveName)
		{
			if (isset($this->primitives[$primitiveName]))
				unset($this->errors[$primitiveName]);
			elseif (isset($this->rules[$primitiveName]))
				unset($this->violated[$primitiveName]);
			else
				throw new MissingElementException(
					$primitiveName.' does not match known primitives or rules'
				);

			return $this;
		}

		/**
		 * Set's custom error mark for primitive.
		 *
		 * @return Form
		**/
		public function markCustom($primitiveName, $customMark)
		{
			Assert::isInteger($customMark);

			$this->errors[$this->get($primitiveName)->getName()] = $customMark;

			return $this;
		}
		//@}

		/**
		 * Returns plain list of error's labels
		**/
		public function getTextualErrors()
		{
			$list = array();

			foreach (array_keys($this->labels) as $name) {
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

		public function getErrorDescriptionFor($name)
		{
			if (
				isset(
					$this->violated[$name],
					$this->describedLabels[$name][$this->violated[$name]]
				)
			)
				return $this->describedLabels[$name][$this->violated[$name]];
			elseif (
				isset(
					$this->errors[$name],
					$this->describedLabels[$name][$this->errors[$name]]
				)
			)
				return $this->describedLabels[$name][$this->errors[$name]];
			else
				return null;
		}

		/**
		 * @return Form
		**/
		public function addErrorDescription($name, $errorType, $description)
		{

			if (
				!isset($this->rules[$name])
				&& !$this->get($name)->getName()
			)
				throw new MissingElementException(
					"knows nothing about '{$name}'"
				);

			$this->describedLabels[$name][$errorType] = $description;

			return $this;
		}

		/**
		 * @return Form
		**/
		public function addWrongLabel($primitiveName, $label)
		{
			return $this->addErrorLabel($primitiveName, Form::WRONG, $label);
		}

		/**
		 * @return Form
		**/
		public function addMissingLabel($primitiveName, $label)
		{
			return $this->addErrorLabel($primitiveName, Form::MISSING, $label);
		}

		/**
		 * @return Form
		**/
		public function addCustomLabel($primitiveName, $customMark, $label)
		{
			return $this->addErrorLabel($primitiveName, $customMark, $label);
		}

		public function getWrongLabel($primitiveName)
		{
			return $this->getErrorLabel($primitiveName, Form::WRONG);
		}

		public function getMissingLabel($primitiveName)
		{
			return $this->getErrorLabel($primitiveName, Form::MISSING);
		}

		/**
		 * @return Form
		**/
		public function import($scope)
		{
			foreach ($this->primitives as $prm)
				$this->importPrimitive($scope, $prm);

			return $this;
		}

		/**
		 * @return Form
		**/
		public function importMore($scope)
		{
			foreach ($this->primitives as $prm) {
				if (!$prm->isImported())
					$this->importPrimitive($scope, $prm);
			}

			return $this;
		}

		/**
		 * @return Form
		**/
		public function importOne($primitiveName, $scope)
		{
			return $this->importPrimitive($scope, $this->get($primitiveName));
		}

		/**
		 * @return Form
		**/
		public function importValue($primitiveName, $value)
		{
			$prm = $this->get($primitiveName);

			return $this->checkImportResult($prm, $prm->importValue($value));
		}

		/**
		 * @return Form
		**/
		public function importOneMore($primitiveName, $scope)
		{
			$prm = $this->get($primitiveName);

			if (!$prm->isImported())
				return $this->importPrimitive($scope, $prm);

			return $this;
		}

		public function exportValue($primitiveName)
		{
			return $this->get($primitiveName)->exportValue();
		}

		public function export()
		{
			$result = array();

			foreach ($this->primitives as $name => $prm) {
				if ($prm->isImported())
					$result[$name] = $prm->exportValue();
			}

			return $result;
		}

		public function toFormValue($value)
		{
			if( $value instanceof MappedFormField )
				return $value->toValue($this);
			elseif ($value instanceof FormField)
				return $this->getValue($value->getName());
			elseif ($value instanceof LogicalObject)
				return $value->toBoolean($this);
			else
				return $value;
		}

		/**
		 * @return Form
		**/
		public function setProto(EntityProto $proto)
		{
			$this->proto = $proto;

			return $this;
		}

		/**
		 * @return EntityProto
		**/
		public function getProto()
		{
			return $this->proto;
		}

		/**
		 * @return Form
		**/
		private function importPrimitive($scope, BasePrimitive $prm)
		{
			if (!$this->importFiltering) {
				if ($prm instanceof FiltrablePrimitive) {

					$chain = $prm->getImportFilter();

					$prm->dropImportFilters();

					$result = $this->checkImportResult(
						$prm,
						$prm->import($scope)
					);

					$prm->setImportFilter($chain);

					return $result;

				} elseif ($prm instanceof PrimitiveForm) {
					return $this->checkImportResult(
						$prm,
						$prm->unfilteredImport($scope)
					);
				}
			}

			return $this->checkImportResult($prm, $prm->import($scope));
		}

		/**
		 * @return Form
		**/
		private function checkImportResult(BasePrimitive $prm, $result)
		{
			if (
				$prm instanceof PrimitiveAlias
				&& $result !== null
			)
				$this->markGood($prm->getInner()->getName());

			$name = $prm->getName();

			if (null === $result) {
				if ($prm->isRequired())
					$this->errors[$name] = self::MISSING;

			} elseif (true === $result) {
				unset($this->errors[$name]);

			} elseif ($error = $prm->getCustomError()) {

				$this->errors[$name] = $error;

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
		 * @throws	MissingElementException
		 * @return	Form
		**/
		private function addErrorLabel($name, $errorType, $label)
		{
			if (
				!isset($this->rules[$name])
				&& !$this->get($name)->getName()
			)
				throw new MissingElementException(
					"knows nothing about '{$name}'"
				);

			$this->labels[$name][$errorType] = $label;

			return $this;
		}

		private function getErrorLabel($name, $errorType)
		{
			// checks for primitive's existence
			$this->get($name);

			if (isset($this->labels[$name][$errorType]))
				return $this->labels[$name][$errorType];

			return null;
		}
	}
?>