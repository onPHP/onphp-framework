<?php
/***************************************************************************
 *   Copyright (C) 2011 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	namespace Onphp\Test;

	final class Base62UtilsTest extends TestCase
	{
		public function testSetChars()
		{
			try {
				\Onphp\Base62Utils::setChars('qwerty');
				$this->fail('Length test failed');
			} catch (\Onphp\WrongArgumentException $e) {
				//ok
			}

			try {
				\Onphp\Base62Utils::setChars(
					'0123456789abcdefghijklmn'
					.'ЭЮЯ'
					.'rstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
				);
				$this->fail('Pattern matching failed');
			} catch (\Onphp\WrongArgumentException $e) {
				//is ok
			}
		}

		public function testOutOfRange()
		{
			try {
				\Onphp\Base62Utils::encode(PHP_INT_MAX + 1);
				$this->fail('Out of range failed');
			} catch (\Onphp\WrongArgumentException $e) {
				//is ok
			}

			try {
				switch(PHP_INT_SIZE) {
					case 4:
						\Onphp\Base62Utils::decode('q1w2e3r'); // 7 symbols
						$this->fail('Wrong: int4 max length of code');
						break;

					case 8:
						\Onphp\Base62Utils::decode('q1w2e3r4t5y6'); // 12 symbols
						$this->fail('Wrong: int8 max length of code');
						break;

					default:
						$this->fail('Wrong: PHP is rock');
						break;
				}

			} catch (\Onphp\WrongArgumentException $e) {
				//is ok
			}

			try {
				\Onphp\Base62Utils::decode('');
				$this->fail('Wrong: min length of code');
			} catch (\Onphp\WrongArgumentException $e) {
				//is ok
			}
		}

		public function testPositiveInteger()
		{
			try {
				\Onphp\Base62Utils::encode(-1);
				$this->fail('Positive integer failed');
			} catch (\Onphp\WrongArgumentException $e) {
				//is ok
			}
		}

		public function testWrongDecode()
		{
			try {
				\Onphp\Base62Utils::decode('abc]');
				$this->fail('Wrong code');
			} catch (\Onphp\WrongArgumentException $e) {
				//is ok
			}
		}

		public function testEncodeDecode()
		{
			$int = \Onphp\Base62Utils::decode('onPHP');
			$this->assertSame($int, 360312369);

			$str = \Onphp\Base62Utils::encode(360312369);
			$this->assertSame('onPHP', $str);
		}
	}
?>