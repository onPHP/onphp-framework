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
	final class OqlLogicalObjectNode extends OqlTerminalNode
	{
		private $object = null;
		
		/**
		 * @return OqlLogicalObjectNode
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return LogicalObject
		**/
		public function getObject()
		{
			return $this->object;
		}
		
		/**
		 * @return OqlLogicalObjectNode
		**/
		public function setObject(LogicalObject $object)
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
		 * @return LogicalObject
		**/
		public function toValue()
		{
			return $this->object;
		}
	}
?>