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
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveHstore extends BasePrimitive
	{
		protected $formMapping	= array();
		
		/**
		 * @return PrimitiveHstore
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
		 * @return Form
		**/
		public function getInnerForm()
		{
			return $this->value;
		}
		
		public function getValue()
		{
			if ($this->value instanceof Form)
				return $this->value->export();
			else
				return array();
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return boolean
		**/
		public function importValue($value)
		{
			Assert::isArray($value);
			
			if (!$this->value instanceof Form)
				$this->value = $this->makeForm();
			
			$this->value->import($value);
			
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
		
		public function exportValue()
		{
			if (!$this->value instanceof Form)
				return null;
			
			return $this->value->export();
		}
		
		/**
		 * @return Form
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