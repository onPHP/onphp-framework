<?php
/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	final class Argument extends CollectionItem
	{
		private $name		= null;
		private $type 		= null;
		private $valueType 	= null;
		
		private $value 		= null;
		
		/**
		 * @return Argument
		**/
		public static function create($name, ArgumentType $type)
		{
			return new self($name, $type);
		}
		
		public function __construct($name, ArgumentType $type)
		{
			if ($type->getId() == ArgumentType::SHORT)
				Assert::isEqual(strlen($name), 1);
			
			$this->id = $this->name = $name;
			$this->type = $type;
			
			// default
			$this->valueType = ArgumentValueType::optional();
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		/**
		 * @return ArgumentType
		**/
		public function getType()
		{
			return $this->type;
		}
		
		public function setValueType(ArgumentValueType $valueType)
		{
			$this->valueType = $valueType;
			
			return $this;
		}
		
		/**
		 * @return ArgumentValueType
		**/
		public function getValueType()
		{
			return $this->valueType;
		}
		
		public function setValue($value)
		{
			$this->value = $value;
			
			return $this;
		}
		
		public function getValue()
		{
			return $this->value;
		}
	}
?>