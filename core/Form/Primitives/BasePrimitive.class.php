<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Parent of every Primitive.
	 * 
	 * @ingroup Primitives
	**/
	abstract class BasePrimitive
	{
		protected $name		= null;
		protected $default	= null;
		protected $value	= null;

		protected $required	= false;

		protected $raw		= null;

		public function __construct($name)
		{
			$this->name = $name;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		public function setName($name)
		{
			$this->name = $name;
			
			return $this;
		}

		public function getDefault()
		{
			return $this->default;
		}
		
		public function setDefault($default)
		{
			$this->default = $default;
			
			return $this;
		}
		
		public function getValue()
		{
			return $this->value;
		}
		
		public function getRawValue()
		{
			return $this->raw;
		}
		
		public function getActualValue()
		{
			if (null !== $this->value)
				return $this->value;
			elseif (null !== $this->raw)
				return $this->raw;

			return $this->default;
		}
		
		public function setValue($value)
		{
			$this->value = $value;
			
			return $this;
		}
		
		public function setRawValue($raw)
		{
			$this->raw = $raw;
			
			return $this;
		}
		
		public function isRequired()
		{
			return $this->required;
		}
		
		public function setRequired($really = false)
		{
			$this->required = (true === $really ? true : false);
			
			return $this;
		}
		
		public function required()
		{
			$this->required = true;
			
			return $this;
		}
		
		public function optional()
		{
			$this->required = false;
			
			return $this;
		}
		
		public function importValue($value)
		{
			return $this->import(array($this->getName() => $value));
		}
		
		protected function import($scope)
		{
			if (
				isset($scope[$this->name]) &&
				$scope[$this->name] !== ''
			) {
				$this->raw = $scope[$this->name];
				
				return true;
			}
			
			return null;
		}
	}
?>