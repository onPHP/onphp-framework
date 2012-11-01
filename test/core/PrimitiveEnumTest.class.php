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

	namespace Onphp\Test;

	final class PrimitiveEnumTest extends TestCase
	{
		public function testIntegerValues()
		{
			$defaultId = 2;
			$importId = 1;

			$default = \Onphp\MimeType::wrap($defaultId);
			$imported = \Onphp\MimeType::wrap($importId);

			$form =
				\Onphp\Form::create()->
				add(
					\Onphp\Primitive::enum('enum')
						->of('\Onphp\MimeType')
						->setDefault($default)
				);
			
			$form->import(array('enum' => $importId));
			
			$this->assertEquals($form->getValue('enum')->getId(), $importId);
			$this->assertSame($form->getValue('enum')->getId(), $importId);

			$this->assertEquals($form->getChoiceValue('enum'), $imported->getName());
			$this->assertSame($form->getChoiceValue('enum'), $imported->getName());

			$form->clean();

			$this->assertNull($form->getValue('enum'));
			$this->assertNull($form->getChoiceValue('enum'));
			$this->assertEquals($form->getActualValue('enum')->getId(), $defaultId);
			$this->assertEquals($form->getActualChoiceValue('enum'), $default->getName());

		}
		
		public function testGetList()
		{
			$primitive = \Onphp\Primitive::enum('enum')->of('\Onphp\MimeType');
			$enum = \Onphp\MimeType::wrap(1);
			
			$this->assertEquals($primitive->getList(), \Onphp\MimeType::getObjectList());
			
			$primitive->setDefault($enum);
			$this->assertEquals($primitive->getList(), \Onphp\MimeType::getObjectList());
			
			$primitive->import(array('enum' => \Onphp\MimeType::getAnyId()));
			$this->assertEquals($primitive->getList(), \Onphp\MimeType::getObjectList());
		}
	}
?>