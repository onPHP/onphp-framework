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

	final class PrimitiveEnumTest extends TestCase
	{
		public function testIntegerValues()
		{
			$form =
				Form::create()->
				add(
					Primitive::enum('enum')->of('MimeType')
				);
			
			$form->import(array('enum' => '1'));
			
			$this->assertEquals($form->getValue('enum')->getId(), 1);
			$this->assertSame($form->getValue('enum')->getId(), 1);
		}
		
		public function testGetList()
		{
			$primitive = Primitive::enum('enum')->of('MimeType');
			$enum = MimeType::wrap(1);
			
			$this->assertEquals($primitive->getList(), MimeType::getObjectList());
			
			$primitive->setDefault($enum);
			$this->assertEquals($primitive->getList(), MimeType::getObjectList());
			
			$primitive->import(array('enum' => MimeType::getAnyId()));
			$this->assertEquals($primitive->getList(), MimeType::getObjectList());
		}
	}
?>