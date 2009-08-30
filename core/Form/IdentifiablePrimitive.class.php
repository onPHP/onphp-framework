<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Primitives
	**/
	abstract class IdentifiablePrimitive extends PrimitiveInteger
	{
		protected $className = null;
		
		abstract public function of($className);
		
		public function setValue($value)
		{
			$className = $this->className;
			
			Assert::isTrue($value instanceof $className);
			
			return parent::setValue($value);
		}
	}
?>