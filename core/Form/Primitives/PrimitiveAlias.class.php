<?php
/****************************************************************************
 *   Copyright (C) 2008 by Konstantin V. Arkhipov                           *
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
	final class PrimitiveAlias extends BasePrimitive
	{
		private $primitive = null;
		
		public function __construct($name, BasePrimitive $prm)
		{
			$this->name = $name;
			$this->primitive = $prm;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		public function getValue()
		{
			return $this->primitive->getValue();
		}
		
		public function getRawValue()
		{
			return $this->primitive->getRawValue();
		}
		
		public function getFormValue()
		{
			if (!$this->primitive->isImported()) {
				if ($this->primitive->getValue() === null)
					return null;
				
				return $this->primitive->exportValue();
			}
			
			return $this->primitive->getRawValue();
		}
		
		/**
		 * @return PrimitiveAlias
		**/
		public function setValue($value)
		{
			$this->primitive->setValue($value);
			
			return $this;
		}
		
		/**
		 * @return PrimitiveAlias
		**/
		public function dropValue()
		{
			$this->primitive->dropValue();
			
			return $this;
		}
		
		/**
		 * @return PrimitiveAlias
		**/
		public function setRawValue($raw)
		{
			$this->primitive->setRawValue($raw);
			
			return $this;
		}
		
		public function isImported()
		{
			return $this->primitive->isImported();
		}
		
		/**
		 * @return PrimitiveAlias
		**/
		public function clean()
		{
			$this->primitive->clean();
			
			return $this;
		}
		
		public function importValue($value)
		{
			return $this->primitive->importValue($value);
		}
		
		public function exportValue()
		{
			return $this->primitive->exportValue();
		}
		
		public function import(array $scope)
		{
			if (array_key_exists($this->name, $scope)) {
				$result = 
					$this->primitive->import(
						array($this->primitive->getName() => $scope[$this->name])
					);

				if ($result)
					$this->primitive->dropError();
				
				return $result;
			}
			

			return null;
		}
	}
?>