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
	abstract class OqlObjectNode extends OqlTerminalNode
	{
		protected $object	= null;
		protected $class	= null;
		
		public function getObject()
		{
			return $this->object;
		}
		
		/**
		 * @return OqlObjectNode
		**/
		public function setObject($object)
		{
			Assert::isInstance($object, $this->class);
			
			$this->object = $object;
			
			return $this;
		}
		
		public function toValue()
		{
			return $this->object;
		}
	}
?>