<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Html
	**/
	final class SgmlOpenTag extends SgmlTag
	{
		private $attributes	= array();
		private $empty		= false;
		
		/**
		 * @return SgmlOpenTag
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return SgmlOpenTag
		**/
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
		
		/**
		 * @return SgmlOpenTag
		**/
		public function setAttribute($name, $value)
		{
			$this->attributes[$name] = $value;
			
			return $this;
		}
		
		public function hasAttribute($name)
		{
			$name = strtolower($name);
			
			return isset($this->attributes[$name]);
		}
		
		public function getAttribute($name)
		{
			$name = strtolower($name);
			
			if (!isset($this->attributes[$name]))
				throw new WrongArgumentException(
					"attribute '{$name}' does not exist"
				);
			
			return $this->attributes[$name];
		}
		
		/**
		 * @return SgmlOpenTag
		**/
		public function dropAttribute($name)
		{
			$name = strtolower($name);
			
			if (!isset($this->attributes[$name]))
				throw new WrongArgumentException(
					"attribute '{$name}' does not exist"
				);
			
			unset($this->attributes[$name]);
			
			return $this;
		}
		
		public function getAttributesList()
		{
			return $this->attributes;
		}
		
		/**
		 * @return SgmlOpenTag
		**/
		public function dropAttributesList()
		{
			$this->attributes = array();
			
			return $this;
		}
	}