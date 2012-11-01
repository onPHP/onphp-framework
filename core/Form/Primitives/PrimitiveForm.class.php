<?php
/***************************************************************************
 *   Copyright (C) 2007-2009 by Ivan Y. Khvostishkov                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Primitives
	**/
	namespace Onphp;

	class PrimitiveForm extends BasePrimitive
	{
		protected $proto = null;
		
		private $composite = false;
		
		/**
		 * @throws WrongArgumentException
		 * @return \Onphp\PrimitiveForm
		 * 
		 * @deprecated You should use ofProto() instead
		**/
		public function of($className)
		{
			Assert::classExists($className);
			
			$protoClass = EntityProto::PROTO_CLASS_PREFIX.$className;
			
			Assert::classExists($protoClass);
			
			return $this->ofProto(Singleton::getInstance($protoClass));
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return \Onphp\PrimitiveForm
		**/
		public function ofProto(EntityProto $proto)
		{
			$this->proto = $proto;
			
			return $this;
		}

		public function ofAutoProto(AbstractProtoClass $proto)
		{
			$this->proto = $proto;

			return $this;
		}
		
		/**
		 * @return \Onphp\PrimitiveForm
		 * 
		 * Either composition or aggregation, it is very important on import.
		**/
		public function setComposite($composite = true)
		{
			$this->composite = ($composite == true);
			
			return $this;
		}
		
		public function isComposite()
		{
			return $this->composite;
		}
		
		public function getClassName()
		{
			return $this->proto->className();
		}
		
		public function getProto()
		{
			return $this->proto;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return \Onphp\PrimitiveForm
		**/
		public function setValue($value)
		{
			Assert::isTrue($value instanceof Form);
			
			return parent::setValue($value);
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return \Onphp\PrimitiveForm
		**/
		public function importValue($value)
		{
			if ($value !== null)
				Assert::isTrue($value instanceof Form);
			
			if (!$this->value || !$this->composite) {
				$this->value = $value;
			} else {
				throw new WrongStateException(
					'composite objects should not be broken'
				);
			}
			
			return ($value->getErrors() ? false : true);
		}
		
		public function exportValue()
		{
			if (!$this->value)
				return null;
			
			return $this->value->export();
		}
		
		public function getInnerErrors()
		{
			if ($this->value)
				return $this->value->getInnerErrors();
			
			return array();
		}
		
		public function import($scope)
		{
			return $this->actualImport($scope, true);
		}
		
		public function unfilteredImport($scope)
		{
			return $this->actualImport($scope, false);
		}
		
		private function actualImport($scope, $importFiltering)
		{
			if (!$this->proto)
				throw new WrongStateException(
					"no proto defined for PrimitiveForm '{$this->name}'"
				);
			
			if (!isset($scope[$this->name]))
				return null;
			
			$this->rawValue = $scope[$this->name];
			
			if (!$this->value || !$this->composite)
				$this->value = $this->proto->makeForm();
			
			if (!$importFiltering) {
				$this->value->
					disableImportFiltering()->
					import($this->rawValue)->
					enableImportFiltering();
			} else {
				$this->value->import($this->rawValue);
			}
			
			$this->imported = true;
			
			if ($this->value->getErrors())
				return false;
			
			return true;
		}
	}
?>