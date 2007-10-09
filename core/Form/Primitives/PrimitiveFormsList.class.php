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

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveFormsList extends PrimitiveForm
	{
		protected $value = array();
		
		public function import($scope)
		{
			if (!$this->className)
				throw new WrongStateException(
					"no class defined for PrimitiveFormsList '{$this->name}'"
				);
			
			if (!BasePrimitive::import($scope))
				return null;
			
			$forms = $scope[$this->name];
			
			if (!is_array($forms))
				return false;
			
			foreach ($forms as $form) {
				if (!($form instanceof Form) || $form->getErrors())
					return false;
			}
			
			$this->value = $forms;
			
			return true;
		}
		
		public function importValue($value)
		{
			Assert::isTrue(current($value) instanceof Form);
					
			return $this->import(
				array($this->name => $value)
			);
		}
	}
?>