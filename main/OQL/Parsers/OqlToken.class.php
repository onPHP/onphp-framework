<?php
/****************************************************************************
 *   Copyright (C) 2008-2009 by Vladlen Y. Koshelev                         *
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
	final class OqlToken
	{
		const NEW_LINE				= 1;
		const STRING				= 2;
		const NUMBER				= 3;
		const BOOLEAN				= 4;
		const NULL					= 5;
		const SUBSTITUTION			= 6;
		const KEYWORD				= 7;
		const AGGREGATE_FUNCTION	= 8;
		const IDENTIFIER			= 9;
		const PARENTHESES			= 10;
		const PUNCTUATION			= 11;
		const COMPARISON_OPERATOR	= 12;
		const ARITHMETIC_OPERATOR	= 13;
		
		private $value		= null;
		private $rawValue	= null;
		private $type		= null;
		private $line		= null;
		private $position	= null;
		
		/**
		 * @return OqlToken
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlToken
		**/
		public static function make($value, $rawValue, $type, $line, $position)
		{
			return
				self::create()->
					setValue($value)->
					setRawValue($rawValue)->
					setType($type)->
					setLine($line)->
					setPosition($position);
		}
		
		/**
		 * @return OqlToken
		**/
		public function setValue($value)
		{
			$this->value = $value;
			
			return $this;
		}
		
		public function getValue()
		{
			return $this->value;
		}
		
		/**
		 * @return OqlToken
		**/
		public function setRawValue($rawValue)
		{
			$this->rawValue = $rawValue;
			
			return $this;
		}
		
		public function getRawValue()
		{
			return $this->rawValue;
		}
		
		/**
		 * @return OqlToken
		**/
		public function setType($type)
		{
			$this->type = $type;
			
			return $this;
		}
		
		public function getType()
		{
			return $this->type;
		}
		
		/**
		 * @return OqlToken
		**/
		public function setLine($line)
		{
			$this->line = $line;
			
			return $this;
		}
		
		public function getLine()
		{
			return $this->line;
		}
		
		/**
		 * @return OqlToken
		**/
		public function setPosition($position)
		{
			$this->position = $position;
			
			return $this;
		}
		
		public function getPosition()
		{
			return $this->position;
		}
	}
?>