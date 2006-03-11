<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Helpers
	**/
	abstract class AbstractProtoClass extends Singleton
	{
		private $map	= array();
		private $type	= null;
		
		abstract public function getForm();
		
		/**
		 * @param $map associative array of primitiveName => (array of) requestType
		**/
		public function setFormMap(/* array */ $map)
		{
			$this->map = array_merge($this->map, $map);
			
			return $this;
		}
		
		public function setDefaultRequestType(RequestType $type)
		{
			$this->type = $type;
			
			return $this;
		}
		
		public function addFormSource($primitiveName, RequestType $type)
		{
			if (!$this->getForm()->primitiveExist($primitiveName))
				throw new WrongArgumentException(
					"primitive '{$primitiveName}' does not exist in form"
				);
			
			$this->map[$primitiveName] = $type;
			
			return $this;
		}
		
		/* void */ public function importForm(HttpRequest $request)
		{
			$form = $this->getForm();
			
			foreach ($form->getPrimitiveList() as $prm) {
				
				$name = $prm->getName();
				
				if (isset($this->map[$name])) {
					if (is_array($this->map[$name])) {
						foreach ($this->map[$name] as $type) {
							$scope = $request->getByType($type);
							$form->importOne($name, $scope);
						}
					} else {
						$scope = $request->getByType($this->map[$name]);
						$form->importOne($name, $scope);
					}
				} elseif ($this->type) {
					$scope = $request->getByType($this->type);
					$form->importOne($name, $scope);
				}
			}
			
			return /* void */;
		}
	}
?>