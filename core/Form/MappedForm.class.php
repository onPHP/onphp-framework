<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
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
		
		public static function create(Form $form)
		{
			return new self($form);
		}
		
		public function __construct(Form $form)
		{
			$this->form = $form;
		}
		
		public function getForm()
		{
			return $this->form;
		}
		
		public function setDefaultType(RequestType $type)
		{
			$this->type = $type;
			
			return $this;
		}
		
		public function addSource($primitiveName, RequestType $type)
		{
			$this->checkExistence($primitiveName);
			
			$this->map[$primitiveName] = $type;
			
			return $this;
		}
		
		/* void */ public function importOne($name, HttpRequest $request)
		{
			$this->checkExistence($name);
			
			if (isset($this->map[$name])) {
				if (is_array($this->map[$name])) {
					foreach ($this->map[$name] as $type) {
						$scope = $request->getByType($type);
						$this->form->importOne($name, $scope);
					}
				} else {
					$scope = $request->getByType($this->map[$name]);
					$this->form->importOne($name, $scope);
				}
			} elseif ($this->type) {
				$scope = $request->getByType($this->type);
				$this->form->importOne($name, $scope);
			}
			
			$this->form->checkRules();
		}
		
		/* void */ public function import(HttpRequest $request)
		{
			foreach ($this->form->getPrimitiveList() as $prm)
				$this->importOne($prm->getName(), $request);
		}
		
		private function checkExistence($name)
		{
			if (!$this->form->primitiveExists($name))
				throw new ObjectNotFoundException(
					"there is no '{$name}' primitive"
				);
		}
	}
?>