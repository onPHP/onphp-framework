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

	final class DTOToScopeConverter extends DTOConverter
	{
		public function createResult()
		{
			return array();
		}
		
		public function alterResult($result)
		{
			return $result;
		}
		
		public function preserveTypeLoss($value, DTOProto $childProto)
		{
			// NOTE: type loss here
			return $this;
		}
		
		public function saveToResult(
			$value, BasePrimitive $primitive, &$result
		)
		{
			Assert::isTrue(!is_object($value));
			
			$result[$primitive->getName()] =  $value;
			
			return $this;
		}
	}
?>