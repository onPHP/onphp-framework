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

	class EntityProto extends Singleton
	{
		const PROTO_CLASS_PREFIX = 'EntityProto';
		
		public function baseProto()
		{
			return null;
		}
		
		public function className()
		{
			return null;
		}
		
		// TODO: think about anonymous primitives and persistant mapping
		// instead of creating new one on each call
		public function getFormMapping()
		{
			return array();
		}
		
		// TODO: use checkConstraints($object, $previousObject = null)
		// where object may be business object, form, scope, etc.
		// NOTE: object may contain errors already
		public function checkConstraints(
			$object, Form $form, $previousObject = null
		)
		{
			return $this;
		}
		
		public function checkPostConstraints(
			$object, Form $form, $previousObject = null
		)
		{
			return $this;
		}
		
		public function isAbstract()
		{
			return false;
		}
		
		public function isInstanceOf(EntityProto $proto)
		{
			return ClassUtils::isInstanceOf(
				$this->className(), $proto->className()
			);
		}
		
		final public function getFullFormMapping()
		{
			$result = $this->getFormMapping();
			
			if ($this->baseProto())
				$result = $result + $this->baseProto()->getFullFormMapping();
			
			return $result;
		}
		
		final public function validate(
			$object, $form, $previousObject = null
		)
		{
			if (($object !== null && is_array($object)) || ($form !== null && is_array($form))) {
				return $this->validateList($object, $form, $previousObject);
			}
			
			if ($object && $this->className()) {
				Assert::isInstance($object, $this->className());
			}
			Assert::isInstance($form, 'Form');
			
			if ($previousObject && $this->className())
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
			$getter = $object
				? $this->getValidateObjectGetter($object)
				: null;
			
			$previousGetter = $previousObject
				? $this->getValidateObjectGetter($previousObject)
				: null;
			
			foreach ($this->getFormMapping() as $primitiveName => $primitive) {
				
				if ($primitive instanceof PrimitiveForm) {
					$proto = $primitive->getProto();
					
					$childForm = $form->getValue($primitive->getName());
					
					$child = $getter ? $getter->get($primitiveName) : null;
					
					$previousChild = $previousGetter
						? $previousGetter->get($primitiveName)
						: null;
					
					if (
						!$proto->validate(
							$child, $childForm, $previousChild
						)
					) {
						$form->markWrong($primitive->getName());
					}
				}
			}
			
			$this->checkPostConstraints($object, $form, $previousObject);
			
			$errors = $form->getErrors();
			
			return empty($errors);
		}
		
		final public function validateList(
			$objectsList, $formsList, $previousObjectsList = null
		)
		{
			if ($objectsList !== null)
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
			$object = null;
			
			foreach ($formsList as $form) {
				
				if ($objectsList) {
					$object = current($objectsList);
					next($formsList);
				}
				
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
		
		/**
		 * @return Form
		 * 
		 * @deprecated you should use PrototypedBuilder to make forms
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
		
		final public function getOwnPrimitive($name)
		{
			$mapping = $this->getFormMapping();
			
			if (!isset($mapping[$name]))
				throw new WrongArgumentException(
					"i know nothing about property '$name'"
				);
			
			return $mapping[$name];
		}
		
		final public function getPrimitive($name)
		{
			try {
				$result = $this->getOwnPrimitive($name);
				
			} catch (WrongArgumentException $e) {
				
				if (!$this->baseProto())
					throw $e;
				
				$result = $this->baseProto()->getPrimitive($name);
			}
			
			return $result;
		}
		
		/**
		 * @param any $object
		 * @return ObjectGetter
		 */
		protected function getValidateObjectGetter($object) {
			return new ObjectGetter($this, $object);
		}
	}
?>