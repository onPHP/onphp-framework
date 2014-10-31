<?php
/***************************************************************************
 *   Copyright (C) by Georgiy T. Kutsurua                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 ***************************************************************************/

	class PrimitiveRuleTest extends TestCase
	{
		public function testMain()
		{

			$form = Form::create()->add(
				Primitive::string('pass')
			)->add(
				Primitive::string('repass')
			);

			$form->add(
				Primitive::rule('correctPass')->setForm(
					$form
				)->setExpression(
					Expression::eq(
						FormField::create('pass'),
						FormField::create('repass')
					)
				)
			);

			$scope = array(
				'pass' => '12345',
				'repass' => '1234',
			);

			$form->import($scope);

			$this->assertEmpty(
				$form->getErrors()
			);

			$form->checkRules();

			$this->assertNotNull($form->getError('correctPass'));

			$errors = $form->getErrors();

			$this->assertTrue(isset($errors['correctPass']));
			$this->assertEquals(BasePrimitive::WRONG, $errors['correctPass']);

			$form->clean();

			$this->assertEmpty($form->getErrors());

			$scope = array(
				'pass' => '12345',
				'repass' => '12345',
			);

			$form->import($scope);

			$this->assertEmpty($form->getErrors());

			$form->checkRules();

			$this->assertEmpty($form->getErrors());

		}
	}
?>