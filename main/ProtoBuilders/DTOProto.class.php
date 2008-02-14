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
		const PROTO_CLASS_PREFIX = 'DtoProto';
		
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
		
		public function checkConstraints(
			$object, Form $form, $previousObject = null
		)
		{
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
		
		final public function validate(
			$object, $form, $previousObject = null
		)
		{
			if (is_array($object)) {
				return $this->validateList($object, $form, $previousObject);
			}
			
			Assert::isInstance($object, $this->className());
			Assert::isInstance($form, 'Form');
			
			if ($previousObject)
				Assert::isInstance($previousObject, $this->className());
			
			if ($this->baseProto())
				$this->baseProto()->
					validate($object, $form, $previousObject);
			
			return $this->validateSelf($object, $form, $previousObject);
		}
		
		final public function validateSelf(
			$object, $form, $previousObject = null
		)
		{
			$this->checkConstraints($object, $form, $previousObject);
			
			$getter = new ObjectGetter($this, $object);
			
			$previousGetter = $previousObject
				? new ObjectGetter($this, $previousObject)
				: null;
			
			foreach ($this->getFormMapping() as $id => $primitive) {
				
				if ($primitive instanceof PrimitiveForm) {
					$proto = $primitive->getProto();
					
					$childForm = $form->getValue($primitive->getName());
					$child = $getter->get($id);
					$previousChild = $previousGetter
						? $previousGetter->get($id)
						: null;
					
					$childResult = true;
					
					if (
						$child
						&& !$proto->validate(
							$child, $childForm, $previousChild
						)
					) {
						$form->markWrong($primitive->getName());
					}
				}
			}
			
			$errors = $form->getErrors();
			
			return empty($errors);
		}
		
		final public function validateList(
			$objectsList, $formsList, $previousObjectsList = null
		)
		{
			Assert::isEqual(count($objectsList), count($formsList));
			
			reset($formsList);
			
			if ($previousObjectsList) {
				Assert::isEqual(
					count($objectsList), count($previousObjectsList)
				);
				
				reset($previousObjectsList);
			}
			
			$result = true;
			
			$previousObject = null;
			
			foreach ($objectsList as $object) {
				
				$form = current($formsList);
				next($formsList);
				
				if ($previousObjectsList) {
					$previousObject = current($previousObjectsList);
					next($previousObjectsList);
				}
				
				if (!$this->validate($object, $form, $previousObject))
					$result = false;
			}
			
			return $result;
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
		
		/**
		 * @return Form
		**/
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
		
		/**
		 * @return Form
		**/
		final public function attachPrimitives(Form $form)
		{
			foreach ($this->getFormMapping() as $primitive)
				$form->add($primitive);
			
			return $form;
		}
		
		final public function toForm(DTOClass $dto)
		{
			return DTOToFormImporter::create($this)->
				make($dto);
		}
		
		final public function makeObject(Form $form)
		{
			return FormToObjectConverter::create($this)->
				make($form);
		}
		
		final public function makeDto($object)
		{
			return ObjectToDTOConverter::create($this)->
				make($object);
		}
		
		final public function fillObject(Form $form, $object)
		{
			return FormToObjectConverter::create($this)->
				fillOwn($form, $object);
		}
		
		final public function buildScope(DTOClass $dto)
		{
			return DTOToScopeConverter::create($this)->
				make($dto);
		}
	}
?>