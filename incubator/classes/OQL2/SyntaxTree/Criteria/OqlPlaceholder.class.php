<?php
/****************************************************************************
 *   Copyright (C) 2009 by Vladlen Y. Koshelev                              *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * @ingroup OQL
	**/
	final class OqlPlaceholder implements MappableObject
	{
		private $name	= null;
		private $value	= null;
		private $binded	= false;
		
		/**
		 * @return OqlPlaceholder
		**/
		public static function create($name)
		{
			return new self($name);
		}
		
		public function __construct($name)
		{
			Assert::isScalar($name);
			Assert::isNotSame($name, '');
			
			$this->name = $name;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		public function getValue()
		{
			return $this->value;
		}
		
		public function isBinded()
		{
			return $this->binded;
		}
		
		/**
		 * @return OqlPlaceholder
		**/
		public function bind($value)
		{
			$this->value = $value;
			$this->binded = true;
			
			return $this;
		}
		
		/**
		 * @return MappableObject
		**/
		public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
		{
			Assert::isTrue($this->binded);
			
			$value = $this->value;
			if ($value instanceof Identifiable)
				$value = $value->getId();
			elseif (is_array($value))
				return $value;
			
			return $dao->guessAtom($value, $query);
		}
		
		public function toDialectString(Dialect $dialect)
		{
			if ($dialect instanceof ImaginaryDialect)
				return '$'.$this->name;
			
			Assert::isTrue($this->binded);
			
			$value = $this->value;
			if ($value instanceof Identifiable)
				return $value->getId();
			
			return $value;
		}
		
		public function __toString()
		{
			return $this->binded
				? $this->value
				: '$'.$this->name;
		}
	}
?>