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

namespace OnPHP\Tests\Core;

use OnPHP\Core\Form\Form;
use OnPHP\Core\Form\Primitive;
use OnPHP\Main\Base\MimeType;
use OnPHP\Tests\TestEnvironment\TestCase;

/**
 * @group core
 * @group form
 */
final class PrimitiveEnumTest extends TestCase
{
	public function testIntegerValues()
	{
		$defaultId = 2;
		$importId = 1;

		$default = MimeType::wrap($defaultId);
		$imported = MimeType::wrap($importId);

		$form =
			Form::create()->
			add(
				Primitive::enum('enum')->
					of(MimeType::class)->
					setDefault($default)
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
		$primitive = Primitive::enum('enum')->of(MimeType::class);
		$enum = MimeType::wrap(1);

		$this->assertEquals($primitive->getList(), MimeType::getObjectList());

		$primitive->setDefault($enum);
		$this->assertEquals($primitive->getList(), MimeType::getObjectList());

		$primitive->import(array('enum' => MimeType::getAnyId()));
		$this->assertEquals($primitive->getList(), MimeType::getObjectList());
	}
}
?>