<?php
/****************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * Common Primitive-handling.
	 * 
	 * @ingroup Form
	 * @ingroup Module
	**/
	abstract class PlainForm
	{
		protected $primitives = array();
		
		/**
		 * @return Form
		**/
		public function clean()
		{
			foreach ($this->primitives as $prm)
				$prm->clean();
			
			return $this;
		}
		
		public function exists($name)
		{
			return isset($this->primitives[$name]);
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return Form
		**/
		public function add(BasePrimitive $prm)
		{
			$name = $prm->getName();
			
			Assert::isFalse($this->exists($name), $name.' already exists');
			
			$this->primitives[$name] = $prm;
			
			return $this;
		}
		
		/**
		 * @return Form
		**/
		public function set(BasePrimitive $prm)
		{
			$this->primitives[$prm->getName()] = $prm;
			
			return $this;
		}
		
		/**
		 * @throws MissingElementException
		 * @return Form
		**/
		public function drop($name)
		{
			if (!$this->exists($name))
				throw new MissingElementException(
					"can not drop inexistent primitive '{$name}'"
				);
			
			unset($this->primitives[$name]);
			
			return $this;
		}
		
		/**
		 * @throws MissingElementException
		 * @return BasePrimitive
		**/
		public function get($name)
		{
			if ($this->exists($name))
				return $this->primitives[$name];
			
			throw new MissingElementException("knows nothing about '{$name}'");
		}
		
		/**
		 * @example
		 * array('superFormsList', 5, 'subForm', 'primitiveName') =>
		 * 'superFormsList[5][subForm][primitiveName]'
		**/
		public function getFormName($path)
		{
			// just checking for existence:
			$this->getInner($path);
			
			$path = $this->getInnerPath($path);
			
			$result = array_shift($path);
			
			Assert::isScalar($result);
			
			foreach ($path as $key) {
				Assert::isScalar($key);
				
				$result .= '['.$key.']';
			}
			
			return $result;
		}
		
		/**
		 * @example
		 * array('superFormsList', 5, 'subForm', 'primitiveName') =>
		 * 'superFormsList:5:subForm:primitiveName'
		**/
		public function getFormId($path)
		{
			$this->getInner($path);
			
			$path = $this->getInnerPath($path);
			
			return implode(':', $path);
		}
		
		/**
		 * @throws MissingElementException
		 * @return BasePrimitive
		**/
		public function getInner($path)
		{
			return $this->getInnerForm($path)->
				get($this->getInnerName($path));
		}
		
		public function getInnerName($path)
		{
			$path = $this->getInnerPath($path);
			
			return array_pop($path);
		}
		
		public function getInnerForm($path)
		{
			$path = $this->getInnerPath($path);
			
			$subForm = array_shift($path);
			
			if (!$path) {
				// last element is a name
				return $this;
			}
			
			Assert::isScalar($subForm);
			
			$primitive = $this->get($subForm);
			
			Assert::isInstance($primitive, 'PrimitiveForm');
			
			$subForm = $primitive->getValue();
			
			Assert::isNotNull($subForm);
			
			if ($primitive instanceof PrimitiveFormsList) {
				Assert::isNotEmptyArray($path, 'you must specify index');
				
				$subIndex = array_shift($path);
				
				Assert::isIndexExists($subForm, $subIndex, 'index does not exist');
				
				$subForm = $subForm[$subIndex];
			}
			
			return $subForm->getInnerForm($path);
		}
		
		public function getValue($name)
		{
			return $this->get($name)->getValue();
		}
		
		/**
		 * @return Form
		**/
		public function setValue($name, $value)
		{
			$this->get($name)->setValue($value);
			
			return $this;
		}
		
		public function getRawValue($name)
		{
			return $this->get($name)->getRawValue();
		}
		
		/**
		 * @deprecated by getFormValue
		**/
		public function getActualValue($name)
		{
			return $this->get($name)->getActualValue();
		}
		
		public function getFormValue($name)
		{
			return $this->get($name)->getFormValue();
		}
		
		public function getSafeValue($name)
		{
			return $this->get($name)->getSafeValue();
		}
		
		public function getChoiceValue($name)
		{
			Assert::isTrue(($prm = $this->get($name)) instanceof ListedPrimitive);
			
			return $prm->getChoiceValue();
		}
		
		public function getActualChoiceValue($name)
		{
			Assert::isTrue(($prm = $this->get($name)) instanceof ListedPrimitive);
			
			return $prm->getActualChoiceValue();
		}
		
		/**
		 * @deprecated by getFormValue
		**/
		public function getDisplayValue($name)
		{
			$primitive = $this->get($name);
			
			if ($primitive instanceof FiltrablePrimitive)
				return $primitive->getDisplayValue();
			else
				return $primitive->getActualValue();
		}
		
		public function getNameList()
		{
			return array_keys($this->primitives);
		}
		
		public function getList()
		{
			return $this->primitives;
		}
		
		private function getInnerPath($path)
		{
			if (is_scalar($path))
				$path = array($path);
			
			Assert::isArray($path, 'path must be an array');
			
			Assert::isNotEmptyArray($path, 'empty path is erroneous');
			
			return $path;
		}
	}
?>