<?php
/***************************************************************************
 *   Copyright (C) by Georgiy T. Kutsurua                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 ***************************************************************************/

	class ProtoTest extends TestCase
	{

		public function testGeneratePrimitive()
		{
			$name = 'email';
			$columnName = 'email';
			$type = 'string';
			$className=null;
			$size = 255;
			$required = true;
			$generic = true;
			$inner = false;
			$relationId = null;
			$strategyId = null;

			$label = 'Label for email';
			$description = 'This is description for email';

			$metProperty = LightMetaProperty::fill(
				new LightMetaProperty(),
				$name, $columnName, $type, $className, $size,
				$required, $generic, $inner, $relationId, $strategyId, $label, $description
			);

			$prm = $metProperty->makePrimitive($name);

			$this->assertEquals(
				$size,
				$prm->getMax()
			);

			$this->assertEquals(
				$label,
				$prm->getLabel()
			);

			$this->assertEquals(
				$description,
				$prm->getDescription()
			);

			$this->assertEquals(
				$name,
				$prm->getName()
			);
		}

	}
