<?php
/***************************************************************************
 *   Copyright (C) 2012 by Georgiy T. Kutsurua                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
	
	final class PrimitiveUuidIdentifierTest extends TestCase
	{
		public function testEmpty()
		{
			$prm = Primitive::uuidIdentifier('name')->of('TestUuidObject');
			
			$nullValues = array(null, '');
			foreach ($nullValues as $value) {
				$this->assertNull($prm->import(array('name' => $value)));
				$this->assertNull($prm->importValue($value));
			}
			
			$emptyValues = array(0, '0', false, '550j8400-e29b-41d4-a716-446655440000');
			
			foreach ($emptyValues as $value) {
				$this->assertFalse($prm->import(array('name' => $value)));
				$this->assertFalse($prm->importValue($value));
			}
		}
	}
?>