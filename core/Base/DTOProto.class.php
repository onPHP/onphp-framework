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
						$this->mapScope(
							$this->buildScope($object)
						)
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
		
		protected function getFormMapping()
		{
			return array();
		}
		
		protected function buildScope(DTOClass $object)
		{
			return array();
		}
		
		protected function fillObject(Form $form, $object)
		{
			return $object;
		}
		
		private function attachPrimitives(Form $form)
		{
			foreach ($this->getFormMapping() as $field => $primitive) {
				$form->add($primitive);
			}
			
			return $form;
		}
		
		private function mapScope($scope)
		{
			$result = array();
			
			$formMapping = $this->getFormMapping();
			
			foreach ($scope as $id => $value) {
				$result[$formMapping[$id]->getName()] = $value;
			}
			
			return $result;
		}
	}
?>