<?php
/***************************************************************************
 *   Copyright (C) by Georgiy T. Kutsurua                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 ***************************************************************************/

	class PrimitiveLabelsTest extends TestCase
	{

		public function testLabels()
		{
			$prm = Primitive::string('someString')->setMax(3);

			$label = 'Some string label';
			$prm->setLabel($label);
			$this->assertEquals($label, $prm->getLabel());

			$description = 'Some string description';
			$prm->setDescription($description);
			$this->assertEquals($description, $prm->getDescription());

			$missingLabel = 'You must complete this field';
			$prm->setMissingLabel($missingLabel);
			$prm->markMissing();
			$this->assertEquals($missingLabel, $prm->getActualErrorLabel());

			$prm->clean();

			$this->assertNull($prm->getActualErrorLabel());

			$wrongLabel = 'The error in the field';
			$prm->setWrongLabel($wrongLabel);
			$prm->markWrong();
			$this->assertEquals($wrongLabel, $prm->getActualErrorLabel());

			$prm->clean();

			$customErrorLabel = 'The custom error in the field';
			$customError = 25;
			$prm->setErrorLabel($customError, $customErrorLabel);
			$prm->setError($customError);
			$this->assertEquals($customErrorLabel, $prm->getActualErrorLabel());


		}

	}