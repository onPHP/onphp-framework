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
namespace OnPHP\Tests\Main;

use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Main\Crypto\Base62Utils;
use OnPHP\Tests\TestEnvironment\TestCase;

final class Base62UtilsTest extends TestCase
{
	public function testSetCharsLengthFailed()
	{
		$this->expectException(WrongArgumentException::class);
		Base62Utils::setChars('qwerty');	
	}

	public function testSetCharsPatternMatching()
	{
		$this->expectException(WrongArgumentException::class);
		Base62Utils::setChars(
			'0123456789abcdefghijklmn'
			.'ЭЮЯ'
			.'rstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
		);
	}
	
	public function testOutOfRangeFailed()
	{
		$this->expectException(WrongArgumentException::class);
		Base62Utils::encode(PHP_INT_MAX + 1);
	}

	public function testMaxLengthOfCode()
	{
		$this->expectException(WrongArgumentException::class);
		switch(PHP_INT_SIZE) {
			case 4:
				Base62Utils::decode('q1w2e3r'); // 7 symbols
				$this->fail('Wrong: int4 max length of code');
				break;

			case 8:
				Base62Utils::decode('q1w2e3r4t5y6'); // 12 symbols
				$this->fail('Wrong: int8 max length of code');
				break;

			default:
				$this->fail('Wrong: PHP is rock');
				break;
		}
	}

	public function testMinLengthOfCode()
	{
		$this->expectException(WrongArgumentException::class);
		Base62Utils::decode('');
	}
	
	public function testPositiveInteger()
	{
		$this->expectException(WrongArgumentException::class);
		Base62Utils::encode(-1);
	}

	public function testWrongDecode()
	{
		$this->expectException(WrongArgumentException::class);
		Base62Utils::decode('abc]');
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