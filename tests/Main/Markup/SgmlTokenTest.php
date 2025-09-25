<?php
/***************************************************************************
 *   Copyright (C) 2021 by Sergei V. Deriabin                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Tests\Main\Markup;

use TypeError;
use OnPHP\Main\Markup\Html\SgmlToken;
use OnPHP\Tests\TestEnvironment\TestCase;

class SgmlTokenTest extends TestCase
{
	public function testCreate()
	{
		$token = SgmlToken::create();
		$this->assertInstanceOf(SgmlToken::class, $token);
		$this->assertNull($token->getValue());

		$token = new SgmlToken();
		$this->assertInstanceOf(SgmlToken::class, $token);
		$this->assertNull($token->getValue());
	}

	public function testValue()
	{
		$token = SgmlToken::create();
		$token->setValue('test');
		$this->assertEquals('test', $token->getValue());
		$token->setValue(null);
		$this->assertNull($token->getValue());
		$this->expectException(TypeError::class);
		$token->setValue(function() { return true; });
	}
}