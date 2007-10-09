<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Khvostishkov                               *
 *   dedmajor@oemdesign.ru                                                 *
 ***************************************************************************/
/* $Id$ */

	class DTOProto extends Singleton
	{
		final public function toForm($object)
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
		
		public function baseProto()
		{
			return null;
		}
		
		protected function getFormMapping()
		{
			return array();
		}
		
		protected function buildScope($object)
		{
			return array();
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