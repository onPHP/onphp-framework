<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class SgmlOpenTag extends SgmlTag
	{
		private $attributes = array();
		private $empty		= false;
		
		public static function create()
		{
			return new self;
		}
		
		public function setEmpty($isEmpty)
		{
			Assert::isBoolean($isEmpty);
		
			$this->empty = $isEmpty;
			
			return $this;
		}
		
		public function isEmpty()
		{
			return $this->empty;
		}
		
		public function addAttribute($name, $value)
		{
			Assert::isString($name);
			Assert::isString($value);
			
			$this->attributes[$name] = $value;
		}
		
		public function hasAttribute($name)
		{
			return isset($this->attributes[$name]);
		}
		
		public function getAttribute($name)
		{
			if (!isset($this->attributes[$name]))
				throw new WrongArgumentException(
					"attribute '{$name}' does not exist"
				);
				
			return $this->attribute[$name];
		}
		
		public function dropAttribute($name)
		{
			if (!isset($this->attributes[$name]))
				throw new WrongArgumentException(
					"attribute '{$name}' does not exist"
				);
			
			unset($this->attribute[$name]);
		}
		
		public function getAttributesList()
		{
			return $this->attributes;
		}
		
		public function dropAttributesList()
		{
			$this->attributes = array();
			
			return $this;
		}
	}