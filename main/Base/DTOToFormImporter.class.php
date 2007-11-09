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

	final class DTOToFormImporter extends DTOToFormConverter
	{
		protected function createResult()
		{
			return $this->proto->makeForm();
		}
		
		protected function alterResult($result)
		{
			Assert::isInstance($result, 'Form');
			
			$this->proto->attachPrimitives($result);
			
			return $result;
		}
		
		protected function saveToResult(
			$value, BasePrimitive $primitive, &$result
		)
		{
			Assert::isInstance($result, 'Form');
			
			if ($primitive instanceof PrimitiveForm)
				// inner form has been already imported
				$result->importValue($primitive->getName(), $value);
				
			else
				$result->importOne(
					$primitive->getName(),
					array($primitive->getName() => $value)
				);
			
			return $this;
		}
	}
?>