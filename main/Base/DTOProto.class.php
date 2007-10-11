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
					
					if ($primitive instanceof PrimitiveFormsList) {
						$value = $proto->makeDtosList($value);
					} else {
						$value = $proto->makeDto($value);
					}
					
				} elseif (is_object($value)) {
					if (
						$value instanceof Identifiable
						&& $primitive instanceof PrimitiveIdentifier
					) {
						$value = $value->getId();
						
					} elseif (
						$value instanceof Stringable
					) {
						$value = $value->toString();
						
					} else
						throw new WrongArgumentException(
							'don\'t know how to convert '.get_class($value)
							.' to dto value of primitive '.get_class($primitive)
						);
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
			}
			
			return $result;
		}
		
		protected function getFormMapping()
		{
			return array();
		}
	}
?>