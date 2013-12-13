<?php
/***************************************************************************
 *   Copyright (C) 2011 by Evgeniy N. Sokolov                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	namespace Onphp\Test;

	final class MathUtilsTest extends TestCase
	{
		public function testCompareFloat()
		{
			$this->assertEquals(
				\Onphp\MathUtils::compareFloat(0.001, 0.001),
				0
			);

			$this->assertEquals(
				\Onphp\MathUtils::compareFloat(0, 0.0001, 0.001),
				0
			);

			$this->assertEquals(
				\Onphp\MathUtils::compareFloat(0.0001, 0.00001, 0.000001),
				1
			);
		}

		public function testMatrixMultiplication()
		{
			$left = array(
				array(1, 2),
				array(3, 5),
				array(2, 4)
			);

			$right = array(
				array(4, 7, 5),
				array(3, 1, 4)
			);

			$this->assertEquals(
				\Onphp\MathUtils::getMmult($left, $right),
				array(
					array(10, 9, 13),
					array(27, 26, 35),
					array(20, 18, 26),
				)
			);

			try {
				\Onphp\MathUtils::getMmult(array(), $right);
				$this->fail("Exception expected here");
			} catch (\Onphp\WrongArgumentException $e) {}

			try {
				\Onphp\MathUtils::getMmult($left, array());
				$this->fail("Exception expected here");
			} catch (\Onphp\WrongArgumentException $e) {}
		}
	}
?>
