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
	 * 
	 * Some use cases:
	 * 
	 * - rawValue: scope value, as it passed to import()
	 * 
	 * - value: 1) object after successful import() or 2) preset
	 * (hard-default) object for editing (meaning is determined by
	 * isImported()). Comfortable for business logic.
	 * 
	 * - exported value: value, converted back to scope value, possible taking
	 * into account import filters (see FiltrablePrimitive).
	 * 
	 * - formValue: either raw value or exported preset value, possible taking
	 * into account display filters (see FiltrablePrimitive). Comfortable for
	 * using in html forms.
	 * 
	 * - default: soft-default object, that may be used instead of value when
	 * import fails (see getSafeValue()). Comfortable for business logic.
	 * 
	 * TODO:
	 * 
	 * - safe exported value: export(getSafeValue()). Comfortable for
	 * soft scope correction with valid raw values.
	 * 
	**/
	abstract class BasePrimitive
	{
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
		
		/**
		 * @deprecated by getFormValue
		**/
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
		
		public function getFormValue()
		{
			if (!$this->imported) {
				if ($this->value === null)
					return null;
				
				return $this->exportValue();
			}
			
			return $this->raw;
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
		**/
		public function dropValue($value)
		{
			$this->value = null;
			
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
		
		protected function import($scope)
		{
			if ($scope)
				Assert::isArray($scope);
			
			if (
				!empty($scope[$this->name])
				|| (
					isset($scope[$this->name])
					&& $scope[$this->name] !== ''
				)
			) {
				$this->raw = $scope[$this->name];
				
				return $this->imported = true;
			}
			
			$this->clean();
			
			return null;
		}
	}
?>