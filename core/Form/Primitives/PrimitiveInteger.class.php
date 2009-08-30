<?php
/****************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * @ingroup Primitives
	**/
	class PrimitiveInteger extends FiltrablePrimitive
	{
		public function getTypeName()
		{
			return 'Integer';
		}
		
		public function isObjectType()
		{
			return false;
		}
	}
?>