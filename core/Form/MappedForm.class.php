<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Form
	**/
	final class MappedForm
	{
		private $form = null;
		private $type = null;
		
		private $map = array();
		
		/**
		 * @return MappedForm
		**/
		public static function create(Form $form)
		{
			return new self($form);
		}
		
		public function __construct(Form $form)
		{
			$this->form = $form;
		}
		
		/**
		 * @return Form
		**/
		public function getForm()
		{
			return $this->form;
		}
		
		/**
		 * @return MappedForm
		**/
		public function setDefaultType(RequestType $type)
		{
			$this->type = $type;
			
			return $this;
		}
		
		/**
		 * @return MappedForm
		**/
		public function addSource($primitiveName, RequestType $type)
		{
			$this->checkExistence($primitiveName);
			
			$this->map[$primitiveName][] = $type;
			
			return $this;
		}
		
		/**
		 * @return MappedForm
		**/
		public function importOne($name, HttpRequest $request)
		{
			$this->checkExistence($name);
			
			$scopes = array();
			
			if (isset($this->map[$name])) {
				foreach ($this->map[$name] as $type) {
					$scopes[] = $request->getByType($type);
				}
			} elseif ($this->type) {
				$scopes[] = $request->getByType($this->type);
			}
			
			$first = true;
			foreach ($scopes as $scope) {
				if ($first) {
					$this->form->importOne($name, $scope);
					$first = false;
				} else
					$this->form->importOneMore($name, $scope);
			}
			
			return $this;
		}
		
		/**
		 * @return MappedForm
		**/
		public function import(HttpRequest $request)
		{
			foreach ($this->form->getPrimitiveNames() as $name) {
				$this->importOne($name, $request);
			}
			
			$this->form->checkRules();
			
			return $this;
		}
		
		/**
		 * @return MappedForm
		**/
		public function export(RequestType $type)
		{
			$result = array();
			
			$default = ($this->type == $type);
			
			foreach ($this->form->getPrimitiveList() as $name => $prm) {
				if (
					(
						isset($this->map[$name])
						&& in_array($type, $this->map[$name])
					)
					|| (
						!isset($this->map[$name])
						&& $default
					)
				) {
					if ($prm->getValue())
						$result[$name] = $prm->exportValue();
				}
			}
			
			return $result;
		}
		
		/**
		 * @return MappedForm
		**/
		private function checkExistence($name)
		{
			if (!$this->form->primitiveExists($name))
				throw new MissingElementException(
					"there is no '{$name}' primitive"
				);
			
			return $this;
		}
	}
