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
		
		public function checkConstraints($object)
		{
			return true;
		}
		
		public function isAbstract()
		{
			return false;
		}
		
		final public function createObject()
		{
			$className = $this->className();
			
			return new $className;
		}
		
		final public function createDto()
		{
			$dtoClassName = $this->dtoClassName();
			
			return new $dtoClassName;
		}
		
		final public function toForm(DTOClass $dto)
		{
			$dtoClass = $this->dtoClassName();
			Assert::isInstance($dto, $dtoClass);
			
			return
				$this->
					attachPrimitives(
						$this->baseProto()
							? $this->baseProto()->toForm($dto)
							: Form::create()
					)->
					importMore(
						$this->buildScope($dto)
					);
		}
		
		final public function toFormsList($dtosList)
		{
			if (!$dtosList)
				return null;
			
			Assert::isArray($dtosList);
			
			$result = array();
			
			foreach ($dtosList as $dto) {
				$result[] = $this->toForm($dto);
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
		
		final public function attachPrimitives(Form $form)
		{
			foreach ($this->getFormMapping() as $primitive)
				$form->add($primitive);
			
			return $form;
		}
		
		final public function makeObject(Form $form)
		{
			$className = $this->className();
			
			if ($form->getProto()) {
				$formClassName = $form->getProto()->className();
				
				if (!ClassUtils::isInstanceOf($formClassName, $className))
					throw new WrongArgumentException(
						"proto of class $className cannot work "
						."with form for class $formClassName"
					);
			} else
				$formClassName = null;
			
			if ($formClassName && $this->isAbstract()) {
				if ($formClassName == $className)
					throw new WrongArgumentException(
						'cannot build abstract object of class '
						.getClass($formClassName)
					);
					
				return $form->getProto()->makeObject($form);
			}
			
			return $this->toObject($form, $this->createObject());
		}
		
		final public function toObject(Form $form, $object)
		{
			$class = $this->className();
			Assert::isInstance($object, $class);
			
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
				$result[] = $this->makeObject($form);
			}
			
			return $result;
		}
		
		final public function makeDto($object)
		{
			$class = $this->className();
			Assert::isInstance($object, $class);
			
			return $this->toDto($object, $this->createDto());
		}
		
		final public function makeDtosList($objects)
		{
			if (!$objects)
				return null;
			
			Assert::isArray($objects);
			
			$result = array();
			
			foreach ($objects as $object) {
				$result[] = $this->makeDto($object);
			}
			
			return $result;
		}
		
		final public function toDto($object, $dto)
		{
			$class = $this->className();
			Assert::isInstance($object, $class);
			
			$dtoClass = $this->dtoClassName();
			Assert::isInstance($dto, $dtoClass);
			
			if ($this->baseProto())
				$this->baseProto()->toDto($object, $dto);
			
			foreach ($this->getFormMapping() as $field => $primitive) {
				$getter = 'get'.ucfirst($field);
				$value = $object->$getter();
				
				$setter = 'set'.ucfirst($primitive->getName());
				
				if ($primitive instanceof PrimitiveForm) {
					
					$proto = Singleton::getInstance(
						'Proto'.$primitive->getClassName()
					);
					
					$protoClassName = $proto->className();
					
					if ($primitive instanceof PrimitiveFormsList) {
						$value = $proto->makeDtosList($value);
						
					} else {
						if (
							$proto->isAbstract()
							&& ClassUtils::isInstanceOf($value, $protoClassName)
						) {
							if (get_class($value) == $protoClassName)
								throw new WrongArgumentException(
									'cannot build scope from abstract DTO class '
									.get_class($value)
								);
								
							$proto = $value->proto();
						}
						
						$value = $proto->makeDto($value);
					}
					
				} elseif (is_object($value)) {
					
					$value = $this->dtoValue($value);
					
				} elseif (is_array($value) && is_object(current($value))) {
					
					$dtoValue = array();
					
					foreach ($value as $oneValue) {
						Assert::isTrue(
							is_object($oneValue),
							'array must contain only objects'
						);
						
						$dtoValue[] = $this->dtoValue($oneValue);
					}
					
					$value = $dtoValue;
				}
				
				$dto->$setter($value);
			}
			
			return $dto;
		}
		
		final public function fillObject(Form $form, $object)
		{
			$reflection = new ReflectionClass($object);
			
			foreach ($this->getFormMapping() as $field => $primitive) {
				try {
					$value = $form->getValue($primitive->getName());
				} catch (MissingElementException $e) {
					continue;
				}
				
				$setter = 'set'.ucfirst($field);
				$dropper = 'drop'.ucfirst($field);
				
				if (!$primitive->isRequired() && $value === null) {
					if (
						$primitive instanceof PrimitiveForm
						|| $reflection->hasMethod($dropper)
					) {
						$object->$dropper();
					} else {
						$object->$setter(null);
					}
				} else {
				
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
					
					$object->$setter($value);
				}
			}
			
			return $object;
		}
		
		final protected function buildScope(DTOClass $dto)
		{
			$result = array();
			
			foreach ($this->getFormMapping() as $primitive) {
				
				$methodName = 'get'.ucfirst($primitive->getName());
				$value = $dto->$methodName();
				
				if ($primitive->isRequired() || $value !== null) {
					
					// TODO: primitives refactoring
					if (
						($primitive instanceof PrimitiveFormsList)
						|| ($primitive instanceof PrimitiveEnumerationList)
						|| ($primitive instanceof PrimitiveIdentifierList)
						|| ($primitive instanceof PrimitiveArray)
					) {
						if (!is_array($value))
							$value = array($value);
					}
						
					if ($primitive instanceof PrimitiveForm) {
						
						$proto = Singleton::getInstance(
							'Proto'.$primitive->getClassName()
						);
						
						if ($primitive instanceof PrimitiveFormsList) {
							$value = $proto->toFormsList($value);
							
						} else {
							
							$protoDtoClass = $proto->dtoClassName();
							
							if (
								$proto->isAbstract()
								&& ClassUtils::isInstanceOf($value, $protoDtoClass)
							) {
								if (get_class($value) == $protoDtoClass)
									throw new WrongArgumentException(
										'cannot build scope from abstract DTO class '
										.get_class($value)
									);
									
								$proto = $value->proto();
								
								$formClassName = $proto->className();
							}
							
							$value = $proto->toForm($value);
							
							$value->setProto($proto);
						}
					}
					
					$result[$primitive->getName()] = $value;
				}
			}
			
			return $result;
		}
		
		protected function getFormMapping()
		{
			return array();
		}
		
		private function dtoValue($value)
		{
			$result = null;
			
			if ($value instanceof Identifiable) {
				$result = $value->getId();
				
			} elseif (
				$value instanceof Stringable
			) {
				$result = $value->toString();
				
			} else
				throw new WrongArgumentException(
					'don\'t know how to convert '.get_class($value)
					.' to dto value of primitive '.get_class($primitive)
				);
			
			return $result;
		}
	}
?>