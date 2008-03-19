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

	abstract class FormBuilder extends PrototypedBuilder
	{
		/**
		 * @return Form
		**/
		protected function createEmpty()
		{
			return Form::create();
		}
		
		/**
		 * @return Form
		**/
		protected function prepareOwn($result)
		{
			Assert::isInstance($result, 'Form');
			
			foreach ($this->getFormMapping() as $primitive) {
				if (
					$primitive instanceof PrimitiveForm
					&& $result->primitiveExists($primitive->getName())
				) {
					
					Assert::isEqual(
						$primitive->getProto(),
						$result->get($primitive->getName())->getProto()
					);
					
					continue;
				}
				
				$result->add($primitive);
			}
				
			return $result;
		}
		
		/**
		 * @return FormBuilder
		**/
		protected function preserveTypeLoss($result)
		{
			Assert::isInstance($result, 'Form');
			
			$result->setProto($this->proto);
			
			return $this;
		}
	}
?>