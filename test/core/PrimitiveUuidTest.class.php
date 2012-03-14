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

	final class PrimitiveUuidTest extends TestCase
	{
		public function testImport()
		{
			$prm = Primitive::uuid('name');
			
			$nullValues = array(null, '');
			
			foreach ($nullValues as $value)
				$this->assertNull($prm->importValue($value));
			
			$falseValues = array('550j8400-e29b-41d4-a716-446655440000', $prm);
			
			foreach ($falseValues as $value)
				$this->assertFalse($prm->importValue($value));
			
			$trueValues = array('550e8400-e29b-41d4-a716-446655440000', '550f8400-e29b-41d4-A716-446155440000');
			
			foreach ($trueValues as $value)
				$this->assertTrue($prm->importValue($value));
		}

	}
?>