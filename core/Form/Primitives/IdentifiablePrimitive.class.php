<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Primitives
	**/
	namespace Onphp;

	abstract class IdentifiablePrimitive
		extends PrimitiveInteger // parent class doesn't really matter here
	{
		protected $className = null;
		
		/**
		 * due to historical reasons, by default we're dealing only with
		 * integer identifiers, this problem correctly fixed in master branch
		*/
		protected $scalar = false;
		
		abstract public function of($className);
		
		/**
		 * @return string
		**/
		public function getClassName()
		{
			return $this->className;
		}
		
		/**
		 * @return \Onphp\IdentifiablePrimitive
		**/
		public function setScalar($orly = false)
		{
			$this->scalar = ($orly === true);
			
			return $this;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return \Onphp\IdentifiablePrimitive
		**/
		public function setValue($value)
		{
			$className = $this->className;
			
			Assert::isNotNull($this->className);
			
			Assert::isTrue($value instanceof $className);
			
			return parent::setValue($value);
		}
		
		protected static function guessClassName($class)
		{
			if (is_string($class))
				return $class;
			elseif (is_object($class)) {
				if ($class instanceof Identifiable)
					return get_class($class);
				elseif ($class instanceof GenericDAO)
					return $class->getObjectName();
			}
			
			throw new WrongArgumentException('strange class given - '.$class);
		}
		
		public function exportValue()
		{
			if (!$this->value)
				return null;
			
			return $this->value->getId();
		}
		
		/* void */ protected function checkNumber($number)
		{
			if ($this->scalar)
				Assert::isScalar($number);
			else
				Assert::isInteger($number);
		}
		
		protected function castNumber($number)
		{
			if (!$this->scalar && Assert::checkInteger($number))
				return (int) $number;
			
			return $number;
		}
	}
?>