<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Khvostishkov                               *
 *   dedmajor@oemdesign.ru                                                 *
 ***************************************************************************/
/* $Id$ */

	class DTOProto extends Singleton
	{
		public function baseProto()
		{
			return null;
		}
		
		public function className()
		{
			return null;
		}
		
		public function dtoClassName()
		{
			return null;
		}
		
		final public function createObject()
		{
			$className = $this->className();
			
			return new $className;
		}
		
		final public function toForm(DTOClass $object)
		{
			return
				$this->
					attachPrimitives(
						$this->baseProto()
							? $this->baseProto()->toForm($object)
							: Form::create()
					)->
					importMore(
						$this->buildScope($object)
					);
		}
		
		final public function toFormsList($objectsList)
		{
			if (!$objectsList)
				return null;
			
			Assert::isArray($objectsList);
			
			$result = array();
			
			foreach ($objectsList as $object) {
				$result[] = $this->toForm($object);
			}
			
			return $result;
		}
		
		final public function makeForm()
		{
			return
				$this->
					attachPrimitives(
						$this->baseProto()
							? $this->baseProto()->makeForm()
							: Form::create()
					);
		}
		
		final public function makeObject(Form $form)
		{
			return $this->toObject($form, $this->createObject());
		}
		
		final public function toObject(Form $form, $object)
		{
			if ($this->baseProto())
				$this->baseProto()->toObject($form, $object);
			
			return $this->fillObject($form, $object);
		}
		
		final public function makeObjectsList($forms)
		{
			if (!$forms)
				return null;
			
			Assert::isArray($forms);
			
			$result = array();
			
			foreach ($forms as $form) {
				$result[] = $this->toObject($form, $this->createObject());
			}
			
			return $result;
		}
		
		final protected function buildScope(DTOClass $dto)
		{
			$dtoClass = $this->dtoClassName();
			Assert::isTrue($object instanceof $dtoClass);
			
			$result = array();
			
			foreach ($this->getFormMapping() as $primitive) {
				
				$methodName = 'get'.ucfirst($primitive->getName());
				$value = $dto->$methodName;
				
				if ($primitive instanceof PrimitiveForm) {
					
					$proto = Singleton::getInstance(
						'Proto'.$primitive->getClassName()
					);
					
					if ($primitive instanceof PrimitiveFormsList) {
						$value = $proto->toFormsList($value);
					} else {
						$value = $proto->toForm($value);
					}
				}
				
				$result[$primitive->getName()] = $value;
			}
			
			return $result;
		}
		
		final protected function fillObject(Form $form, $object)
		{
			$class = $this->className();
			Assert::isTrue($object instanceof $class);
			
			foreach ($this->getFormMapping() as $field => $primitive) {
				$methodName = 'set'.ucfirst($field);
				$value = $form->getValue($primitive->getName());
				
				if ($primitive instanceof PrimitiveForm) {
					$proto = Singleton::getInstance(
						'Proto'.$primitive->getClassName()
					);
					
					if ($primitive instanceof PrimitiveFormsList) {
						$value = $proto->makeObjectsList($value);
					} else {
						$value = $proto->makeObject($value);
					}
				}
				
				$object->methodName($value);
			}
			
			return $object;
		}
		
		protected function getFormMapping()
		{
			return array();
		}
		
		final private function attachPrimitives(Form $form)
		{
			foreach ($this->getPrimitives() as $primitive) {
				$form->add($primitive);
			}
			
			return $form;
		}
	}
?>