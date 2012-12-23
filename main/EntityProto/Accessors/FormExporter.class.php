<?php
/***************************************************************************
 *   Copyright (C) 2008 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	final class FormExporter extends PrototypedGetter
	{
		public function __construct(EntityProto $proto, $object)
		{
			Assert::isInstance($object, 'Form');
			
			return parent::__construct($proto, $object);
		}
		
		public function get($name)
		{
			if (!isset($this->mapping[$name]))
				throw new WrongArgumentException(
					"knows nothing about property '{$name}'"
				);
			
			$primitive = $this->mapping[$name];
			
			$formPrimitive = $this->object->get($primitive->getName());
			
			if ($primitive instanceof PrimitiveForm) {
				// export of inner forms controlled by builder
				return $formPrimitive->getValue();
			}
			return $formPrimitive->exportValue();
		}
	}
