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
/* $Id$ */

	final class FormHardenedSetter extends PrototypedSetter
	{
		public function __construct(DTOProto $proto, &$object)
		{
			Assert::isInstance($object, 'Form');
			
			return parent::__construct($proto, $object);
		}
		
		public function set($name, $value)
		{
			if (!isset($this->mapping[$name]))
				throw new WrongArgumentException(
					"knows nothing about property '{$name}'"
				);
			
			$primitive = $this->mapping[$name];
			
			$method = ($value === null)
				? 'dropValue'
				: 'setValue';
			
			$this->object->get($primitive->getName())->
				$method($value);
			
			return $this;
		}
	}
?>