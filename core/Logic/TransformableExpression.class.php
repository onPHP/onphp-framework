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

	abstract class TransformableExpression implements LogicalObject
	{
		protected function transformProperty(StorableDAO $dao, $property)
		{
			if ($property instanceof LogicalObject)
				return $property->applyMapping($dao);
			
			$mapping = $dao->getMapping();
			
			if ($mapping[$property] === null)
				return $property;
			
			return $mapping[$property];
		}
	}
?>