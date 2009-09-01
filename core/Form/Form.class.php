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
	final class Form extends PlainForm
	{
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
			$errors = array();
			
			foreach ($this->primitives as $name => $prm)
				if ($error = $prm->getError())
					$errors[$name] = $error;
			
			return $errors;
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
			foreach ($this->primitives as $prm)
				$prm->dropError();
			
			return $this;
		}
		
		public function getError($name)
		{
			return $this->get($name)->getError();
		}
		
		/**
		 * @return Form
		**/
		public function dropError($name)
		{
			$this->get($name)->dropError();
			
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
		 * @throws MissingElementException
		**/
		public function markMissing($name)
		{
			return $this->markCustom($name, BasePrimitive::MISSING);
		}
		
		/**
		 * @return Form
		 * @throws MissingElementException
		**/
		public function markWrong($name)
		{
			return $this->markCustom($name, BasePrimitive::WRONG);
		}
		
		/**
		 * @return Form
		 * @throws MissingElementException
		**/
		public function markGood($name)
		{
			$this->get($name)->dropError();
			
			return $this;
		}
		
		/**
		 * Set's custom error mark for primitive.
		 * 
		 * @return Form
		 * @throws MissingElementException
		**/
		public function markCustom($name, $customMark)
		{
			$this->get($name)->setError($customMark);
			
			return $this;
		}
		//@}
		
		/**
		 * Returns associated list of prm. names => error's labels
		**/
		public function getTextualErrors()
		{
			$list = array();
			
			foreach ($this->primitives as $name => $prm) {
				if ($label = $this->getTextualErrorFor($name))
					$list[$name] = $label;
			}
			
			return $list;
		}
		
		public function getTextualErrorFor($name)
		{
			if (
				$this->exists($name)
				&& ($error = $this->get($name)->getError())
			) {
				if (isset($this->labels[$name][$error]))
					return $this->labels[$name][$error];
			}
			
			return null;
		}
		
		public function getErrorDescriptionFor($name)
		{
			if (
				$this->exists($name)
				&& ($error = $this->get($name)->getError())
				&& isset(
					$this->describedLabels[$name][$error]
				)
			)
				return $this->describedLabels[$name][$error];
			else
				return null;
		}
		
		public function getInnerError($primitivePath)
		{
			return
				$this->getInnerForm($primitivePath)->
					getTextualErrorFor(
						$this->getInnerName($primitivePath)
					);
		}
		
		public function getInnerErrorDescription($primitivePath)
		{
			return
				$this->getInnerForm($primitivePath)->
					getErrorDescriptionFor(
						$this->getInnerName($primitivePath)
					);
		}
		
		/**
		 * @return Form
		**/
		public function addErrorDescription($name, $errorType, $description)
		{
			$this->describedLabels[
				// checks primitive existence
				$this->get($name)->getName()
			][
				$errorType
			] =
				$description;
			
			return $this;
		}
		
		/**
		 * @return Form
		**/
		public function addWrongLabel($primitiveName, $label)
		{
			return $this->addErrorLabel(
				$primitiveName,
				BasePrimitive::WRONG,
				$label
			);
		}
		
		/**
		 * @return Form
		**/
		public function addMissingLabel($primitiveName, $label)
		{
			return $this->addErrorLabel(
				$primitiveName,
				BasePrimitive::MISSING,
				$label
			);
		}
		
		/**
		 * @return Form
		**/
		public function addCustomLabel($primitiveName, $customMark, $label)
		{
			return $this->addErrorLabel(
				$primitiveName,
				$customMark,
				$label
			);
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
		public function import(array $scope)
		{
			foreach ($this->primitives as $prm)
				$this->importPrimitive($scope, $prm);
			
			return $this;
		}
		
		/**
		 * @return Form
		**/
		public function importMore(array $scope)
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
		public function importOne($primitiveName, array $scope)
		{
			return $this->importPrimitive($scope, $this->get($primitiveName));
		}
		
		/**
		 * @return Form
		**/
		public function importValue($primitiveName, $value)
		{
			$prm = $this->get($primitiveName);
			
			return $this->checkImportResult(
				$prm,
				$prm->getValue(),
				$prm->importValue($value)
			);
		}
		
		/**
		 * @return Form
		**/
		public function importOneMore($primitiveName, array $scope)
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
		
		public function getLogicValue($value)
		{
			if ($value instanceof FormField)
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
						$prm->getValue(),
						$prm->import($scope)
					);
					
					$prm->setImportFilter($chain);
					
					return $result;
					
				} elseif ($prm instanceof PrimitiveForm) {
					return $this->checkImportResult(
						$prm,
						$prm->getValue(),
						$prm->unfilteredImport($scope)
					);
				}
			}
			
			return $this->checkImportResult(
				$prm,
				$prm->getValue(),
				$prm->import($scope)
			);
		}
		
		/**
		 * @return Form
		**/
		private function checkImportResult(BasePrimitive $prm, $value, $result)
		{
			$name = $prm->getName();
			
			if (true === $result)
				$prm->dropError();
			else {
				if (null === $result) {
					if ($prm->isRequired())
						$prm->setError(BasePrimitive::MISSING);
				} else {
					$prm->setError(BasePrimitive::WRONG);
				}
				
				if (null <> $value)
					$prm->setValue($value);
			}
			
			return $this;
		}
		
		/**
		 * Assigns specific label for given primitive and error type.
		 * One more example of horrible documentation style.
		 * 
		 * @param	$name		string	primitive name
		 * @param	$errorType	enum	BasePrimitive::(WRONG|MISSING)
		 * @param	$label		string	YDFB WTF is this :-) (c) /.
		 * @throws	MissingElementException
		 * @return	Form
		**/
		private function addErrorLabel($name, $errorType, $label)
		{
			$this->labels[
				// checks primitive existence
				$this->get($name)->getName()
			][
				$errorType
			] = $label;
			
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