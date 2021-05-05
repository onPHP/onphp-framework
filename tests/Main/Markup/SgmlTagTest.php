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

use OnPHP\Main\Markup\Html\SgmlTag;
use OnPHP\Tests\TestEnvironment\TestCase;

class SgmlTagTest extends TestCase
{
	public function testCreate()
	{
		$reflectionClass = new \ReflectionClass(SgmlTag::class);
		$this->assertTrue($reflectionClass->isAbstract());

		$tag = \OnPHP\Tests\TestEnvironment\SgmlTag::create();
		$this->assertInstanceOf(SgmlTag::class, $tag);
		$this->assertNull($tag->getId());

		$tag = new \OnPHP\Tests\TestEnvironment\SgmlTag();
		$this->assertInstanceOf(SgmlTag::class, $tag);
		$this->assertNull($tag->getId());
	}

	public function testValue()
	{
		$tag =
			\OnPHP\Tests\TestEnvironment\SgmlTag::create()
				->setId('p');
		$this->assertEquals('p', $tag->getId());
		$tag->setId('strong');
		$this->assertEquals('strong', $tag->getId());
	}
}