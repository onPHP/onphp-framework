<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Types
	**/
	final class UnsignedBigIntegerType extends BigIntegerType
	{
		public function getSize()
		{
			return 8 & LightMetaProperty::UNSIGNED_FLAG;
		}
		
		public function toColumnType()
		{
			return
				parent::toColumnType()
				."->\n"
				.'setUnsigned(true)';
		}
	}
?>