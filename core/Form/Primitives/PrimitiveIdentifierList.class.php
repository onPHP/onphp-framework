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

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveIdentifierList extends PrimitiveIdentifier
	{
		protected $value = array();
		private $ignoreEmpty = false;
		
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
					if ($this->scalar)
						Assert::isScalar(current($value));
					else
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
		
		public function import($scope)
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
				if ((string) $id == "" && $this->isIgnoreEmpty())
					continue;

				if (
					($this->scalar && !Assert::checkScalar($id))
					|| (!$this->scalar && !Assert::checkInteger($id))
				)
					return false;
				
				$values[] = $id;
			}
			
			$objectList = $this->dao()->getListByIds($values);
			
			if (
				count($objectList) == count($values)
				&& !($this->min && count($values) < $this->min)
				&& !($this->max && count($values) > $this->max)
			) {
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

		public function setIgnoreEmpty($orly = true)
		{
			$this->ignoreEmpty = ($orly === true);

			return $this;
		}

		public function isIgnoreEmpty()
		{
			return $this->ignoreEmpty;
		}
	}
