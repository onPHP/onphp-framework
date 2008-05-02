<?php
/****************************************************************************
 *   Copyright (C) 2007-2008 by Denis M. Gabaidulin, Konstantin V. Arkhipov *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveIdentifierList extends PrimitiveIdentifier
	{
		protected $value = array();
		
		/**
		 * @return PrimitiveIdentifierList
		**/
		public function clean()
		{
			parent::clean();
			
			// restoring our very own default
			$this->value = array();
			
			return $this;
		}
		
		/**
		 * @return PrimitiveIdentifierList
		**/
		public function setValue($value)
		{
			if ($value) {
				Assert::isArray($value);
				Assert::isInstance(current($value), $this->className);
			}
			
			$this->value = $value;
			
			return $this;
		}
		
		public function importValue($value)
		{
			if ($value instanceof UnifiedContainer) {
				if ($value->isLazy())
					return $this->import(
						array($this->name => $value->getList())
					);
				elseif (
					$value->getParentObject()->getId()
					&& ($list = $value->getList())
				) {
					return $this->import(
						array($this->name => ArrayUtils::getIdsArray($list))
					);
				} else {
					return parent::importValue(null);
				}
			}
			
			if (is_array($value)) {
				try {
					Assert::isInteger(current($value));
					
					return $this->import(
						array($this->name => $value)
					);
				} catch (WrongArgumentException $e) {
					return $this->import(
						array($this->name => ArrayUtils::getIdsArray($value))
					);
				}
			}
			
			return parent::importValue($value);
		}
		
		public function import(array $scope)
		{
			if (!$this->className)
				throw new WrongStateException(
					"no class defined for PrimitiveIdentifierList '{$this->name}'"
				);
			
			if (!BasePrimitive::import($scope))
				return null;
			
			if (!is_array($scope[$this->name]))
				return false;
			
			$list = array_unique($scope[$this->name]);
			
			$values = array();
			
			foreach ($list as $id) {
				if (!Assert::checkInteger($id))
					return false;
				
				$values[] = $id;
			}
			
			$objectList = $this->dao()->getListByIds($values);
			
			if (count($objectList) == count($values)) {
				$this->value = $objectList;
				return true;
			}
			
			return false;
		}
		
		public function exportValue()
		{
			if (!$this->value)
				return null;
			
			return ArrayUtils::getIdsArray($this->value);
		}
	}
?>