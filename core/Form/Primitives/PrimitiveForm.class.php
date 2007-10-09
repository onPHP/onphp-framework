<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	class PrimitiveForm extends BasePrimitive
	{
		protected $className = null;
		
		private $info = null;
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveForm
		**/
		public function of($className)
		{
			Assert::isTrue(
				class_exists($className, true),
				"knows nothing about '{$className}' class"
			);
			
			$this->info = new ReflectionClass($className);
			
			$this->className = $className;
			
			return $this;
		}
		
		public function getClassName()
		{
			return $this->className;
		}
		
		public function getReflection()
		{
			return $this->info;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveForm
		**/
		public function setValue($value)
		{
			Assert::isTrue($value instanceof Form);
			
			return parent::setValue($value);
		}
		
		public function import($scope)
		{
			if (!$this->className)
				throw new WrongStateException(
					"no class defined for PrimitiveForm '{$this->name}'"
				);
			
			if (!BasePrimitive::import($scope))
				return null;
			
			$form = $scope[$this->name];
				
			if (!($form instanceof Form) || $form->getErrors())
				return false;
			
			$this->value = $form;
			
			return true;
		}
	}
?>