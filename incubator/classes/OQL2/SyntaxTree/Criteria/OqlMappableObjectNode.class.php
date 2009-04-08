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
	final class OqlMappableObjectNode extends OqlTerminalNode
	{
		private $object = null;
		
		/**
		 * @return OqlMappableObjectNode
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return MappableObject
		**/
		public function getObject()
		{
			return $this->object;
		}
		
		/**
		 * @return OqlMappableObjectNode
		**/
		public function setObject(MappableObject $object)
		{
			$this->object = $object;
			
			return $this;
		}
		
		public function toString()
		{
			return $this->object ?
				$this->object->toDialectString(ImaginaryDialect::me())
				: null;
		}
		
		/**
		 * @return MappableObject
		**/
		public function toValue()
		{
			return $this->object;
		}
	}
?>