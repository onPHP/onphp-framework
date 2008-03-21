<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Parent of every Primitive.
	 * 
	 * @ingroup Primitives
	 * @ingroup Module
	**/
	abstract class BasePrimitive
	{
		const	DELIMITER	= '.';
		
		protected $name		= null;
		protected $default	= null;
		protected $value	= null;
		
		protected $required	= false;
		protected $imported	= false;
		
		protected $raw		= null;
		
		protected $customError	= null;
		
		public function __construct($name)
		{
			$this->name = $name;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		public function getActualName($prefix = null)
		{
			return
				$prefix
					? $prefix.self::DELIMITER.$this->name
					: $this->name;
		}
		
		/**
		 * @return BasePrimitive
		**/
		public function setName($name)
		{
			$this->name = $name;
			
			return $this;
		}
		
		public function getDefault()
		{
			return $this->default;
		}
		
		/**
		 * @return BasePrimitive
		**/
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
			elseif ($this->imported)
				return $this->raw;
			
			return $this->default;
		}
		
		public function getSafeValue()
		{
			if ($this->imported)
				return $this->value;
			
			return $this->default;
		}
		
		/**
		 * @return BasePrimitive
		**/
		public function setValue($value)
		{
			$this->value = $value;
			
			return $this;
		}
		
		/**
		 * @return BasePrimitive
		 * 
		 * usually, you should not use this method
		**/
		public function setRawValue($raw)
		{
			$this->raw = $raw;
			
			return $this;
		}
		
		public function isRequired()
		{
			return $this->required;
		}
		
		/**
		 * @return BasePrimitive
		**/
		public function setRequired($really = false)
		{
			$this->required = (true === $really ? true : false);
			
			return $this;
		}
		
		/**
		 * @return BasePrimitive
		**/
		public function required()
		{
			$this->required = true;
			
			return $this;
		}
		
		/**
		 * @return BasePrimitive
		**/
		public function optional()
		{
			$this->required = false;
			
			return $this;
		}
		
		public function isImported()
		{
			return $this->imported;
		}
		
		/**
		 * @return BasePrimitive
		**/
		public function clean()
		{
			$this->raw = null;
			$this->value = null;
			$this->imported = false;
			
			return $this;
		}
		
		public function importValue($value)
		{
			return $this->import(array($this->getName() => $value));
		}
		
		public function exportValue()
		{
			return $this->value;
		}
		
		public function getCustomError()
		{
			return $this->customError;
		}
		
		protected function import($scope, $prefix = null)
		{
			$name = $this->getActualName($prefix);
			
			if (
				!empty($scope[$name])
				|| (
					isset($scope[$name])
					&& $scope[$name] !== ''
				)
			) {
				$this->raw = $scope[$name];
				
				return $this->imported = true;
			}
			
			$this->clean();
			
			return null;
		}
	}
?>