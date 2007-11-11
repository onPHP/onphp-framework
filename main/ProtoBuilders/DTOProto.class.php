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