<?php
/****************************************************************************
 *   Copyright (C) 2009 by Sergey S. Sergeev                                *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * @ingroup Primitives
	**/
	namespace Onphp;

	final class PrimitiveHstore extends BasePrimitive
	{
		protected $formMapping	= array();
		
		/**
		 * @return \Onphp\PrimitiveHstore
		**/
		public function setFormMapping($array)
		{
			$this->formMapping = $array;

			return $this;
		}
		
		public function getFormMapping()
		{
			return $this->formMapping;
		}
		
		public function getInnerErrors()
		{
			if ($this->value instanceof Form)
				return $this->value->getInnerErrors();
			
			return array();
		}
		
		/**
		 * @return \Onphp\Form
		**/
		public function getInnerForm()
		{
			return $this->value;
		}
		
		public function getValue()
		{
			if (!$this->value instanceof Form)
				return null;
			
			return Hstore::make($this->value->export());
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return boolean
		**/
		public function importValue($value)
		{
			if ($value === null)
				return parent::importValue(null);
			
			Assert::isTrue($value instanceof Hstore, 'importValue');
				
			if (!$this->value instanceof Form)
				$this->value = $this->makeForm();
			
			$this->value->import($value->getList());
			$this->imported = true;
			
			return
				$this->value->getErrors()
					? false
					: true;
		}
		
		public function import($scope)
		{
			if (!isset($scope[$this->name]))
				return null;
			
			$this->rawValue = $scope[$this->name];
			
			if (!$this->value instanceof Form)
				$this->value = $this->makeForm();
			
			$this->value->import($this->rawValue);
			
			$this->imported = true;
			
			if ($this->value->getErrors())
				return false;
			
			return true;
		}
		
		/**
		 * @return \Onphp\Hstore
		**/
		public function exportValue()
		{
			if (!$this->value instanceof Form)
				return null;
			
			return !$this->value->getErrors()
				? $this->value->export()
				: null;
		}
		
		/**
		 * @return \Onphp\Form
		**/
		protected function makeForm()
		{
			$form = Form::create();
			
			foreach ($this->getFormMapping() as $primitive)
				$form->add($primitive);
			
			return $form;
		}
	}
?>