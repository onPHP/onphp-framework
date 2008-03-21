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
		
		public function import($scope, $prefix = null)
		{
			$name = $this->getActualName($prefix);
			
			if (!$this->proto)
				throw new WrongStateException(
					"no proto defined for PrimitiveFormsList '{$name}'"
				);
			
			if (!isset($scope[$name]))
				return null;
			
			$this->rawValue = $scope[$name];
			
			$this->imported = true;
			
			if (!is_array($scope[$name]))
				return false;
			
			$error = false;
			
			$this->value = array();
			
			foreach ($scope[$name] as $id => $value) {
				$this->value[$id] =
					$this->proto->makeForm()->
						import($value);
				
				if ($this->value[$id]->getErrors())
					$error = true;
			}
			
			return !$error;
		}
		
		public function importValue($value)
		{
			if ($value !== null)
				Assert::isArray($value);
			else
				return null;
			
			$result = true;
			
			$resultValue = array();
			
			foreach ($value as $id => $form) {
				Assert::isInstance($form, 'Form');
				
				$resultValue[$id] = $form;
				
				if ($form->getErrors())
					$result = false;
			}
			
			$this->value = $resultValue;
			
			return $result;
		}
		
		public function exportValue()
		{
			if (!$this->isImported())
				return null;
			
			$result = array();
			
			foreach ($this->value as $id => $form) {
				$result[$id] = $form->export();
			}
			
			return $result;
		}
	}
?>