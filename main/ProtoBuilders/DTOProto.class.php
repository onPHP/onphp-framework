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
		const PROTO_CLASS_PREFIX		= 'DtoProto';
		
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
			return $this->className().'DTO';
		}
		
		public function getFormMapping()
		{
			return array();
		}
		
		public function checkConstraints($object)
		{
			Assert::isInstance($object, $this->className());
			
			return true;
		}
		
		public function isAbstract()
		{
			return false;
		}
		
		public function isInstanceOf(DTOProto $proto)
		{
			return ClassUtils::isInstanceOf(
				$this->dtoClassName(), $proto->dtoClassName()
			);
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
		
		final public function toForm(DTOClass $dto)
		{
			$converter = new DTOToFormImporter($this);
			
			return $converter->make($dto);
		}
		
		final public function makeObject(Form $form)
		{
			$proto = $this;
			
			if ($form->getProto()) {
				$formClassName = $form->getProto()->className();
				$className = $this->className();
			
				if (!ClassUtils::isInstanceOf($formClassName, $className))
					throw new WrongArgumentException(
						"proto of class $className cannot work "
						."with form for class $formClassName"
					);
				
				$proto = $form->getProto();
			}
			
			if ($proto->isAbstract()) {
				throw new WrongArgumentException(
					'cannot build abstract object of class '
					.$proto->className()
				);
			}
			
			if ($proto !== $this)
				return $form->getProto()->makeObject($form);
			else
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
			if ($forms === null)
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
			if ($objects === null)
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
						self::PROTO_CLASS_PREFIX.$primitive->getClassName()
					);
					
					$protoClassName = $proto->className();
					
					if ($primitive instanceof PrimitiveFormsList) {
						$value = $proto->makeDtosList($value);
						
					} else {
						if ($value) {
							Assert::isInstance($value, $protoClassName);
							
							$proto = $value->dtoProto();
							
							if ($proto->isAbstract())
								throw new WrongArgumentException(
									'cannot build DTO from '
									.'abstract proto for class '
									.get_class($value)
								);
						}
						
						$value = $proto->makeDto($value);
					}
					
				} elseif (is_object($value)) {
					
					if (
						($primitive instanceof PrimitiveAnyType)
						&& ($value instanceof DTOPrototyped)
					)
						$value = $value->dtoProto()->makeDto($value);
					else
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
				
				if ($value === null) {
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
							self::PROTO_CLASS_PREFIX.$primitive->getClassName()
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
		
		final public function buildScope(DTOClass $dto)
		{
			$converter = new DTOToScopeConverter($this);
			
			// NOTE: type loss here
			return $converter->make($dto);
		}
		
		// TODO: move to Primitive
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
					'don\'t know how to convert to DTO value of class '
					.get_class($value)
				);
			
			return $result;
		}
	}
?>