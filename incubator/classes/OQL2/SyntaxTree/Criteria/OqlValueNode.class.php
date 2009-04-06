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
	final class OqlValueNode extends OqlTerminalNode
	{
		private $value = null;
		
		/**
		 * @return OqlValueNode
		**/
		public static function create()
		{
			return new self;
		}
		
		public function getValue()
		{
			return $this->value;
		}
		
		/**
		 * @return OqlValueNode
		**/
		public function setValue($value)
		{
			$this->value = $value;
			
			return $this;
		}
		
		public function toString()
		{
			return $this->value;
		}
		
		public function toValue()
		{
			return $this->value;
		}
	}
?>