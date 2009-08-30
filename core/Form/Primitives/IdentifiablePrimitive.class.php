<?php
/***************************************************************************
 *   Copyright (C) 2006-2009 by Konstantin V. Arkhipov                     *
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
	abstract class IdentifiablePrimitive extends FiltrablePrimitive
	{
		protected $className = null;
		
		abstract public function of($className);
		
		/**
		 * @throws WrongArgumentException
		 * @return IdentifiablePrimitive
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
	}
?>