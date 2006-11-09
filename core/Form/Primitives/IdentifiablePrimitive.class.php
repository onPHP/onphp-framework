<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	abstract class IdentifiablePrimitive extends PrimitiveInteger
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
			
			Assert::isTrue($value instanceof $className);
			
			return parent::setValue($value);
		}
	}
?>