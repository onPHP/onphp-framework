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

	final class Base62UtilsTest extends TestCase
	{
		public function testSetChars()
		{
			try {
				Base62Utils::setChars('qwerty');
				$this->fail('Length test failed');
			} catch (WrongArgumentException $e) {
				//ok
			}

			try {
				Base62Utils::setChars(
					'0123456789abcdefghijklmn'
					.'ЭЮЯ'
					.'rstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
				);
				$this->fail('Pattern matching failed');
			} catch (WrongArgumentException $e) {
				//is ok
			}
		}

		public function testOutOfRange()
		{
			try {
				Base62Utils::encode(pow(2, 31));
				$this->fail('Out of range failed');
			} catch (WrongArgumentException $e) {
				//is ok
			}
		}

		public function testEncodeDecode()
		{
			$int = Base62Utils::decode('onPHP');
			$this->assertSame($int, 360312369);

			$str = Base62Utils::encode(360312369);
			$this->assertSame('onPHP', $str);
		}
	}
?>