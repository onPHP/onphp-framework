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

use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Main\Markup\Html\SgmlOpenTag;
use OnPHP\Main\Markup\Html\SgmlTag;
use OnPHP\Tests\TestEnvironment\TestCase;

class SgmlOpenTagTest extends TestCase
{
	public function testCreate()
	{
		$reflectionClass = new \ReflectionClass(SgmlOpenTag::class);
		$this->assertTrue($reflectionClass->isFinal());

		$tag = SgmlOpenTag::create();
		$this->assertInstanceOf(SgmlOpenTag::class, $tag);
		$this->assertEmpty($tag->getAttributesList());
		$this->assertFalse($tag->isEmpty());

		$tag = new SgmlOpenTag();
		$this->assertInstanceOf(SgmlOpenTag::class, $tag);
		$this->assertEmpty($tag->getAttributesList());
		$this->assertFalse($tag->isEmpty());
	}

	public function testEmpty()
	{
		$tag = SgmlOpenTag::create();
		$tag->setEmpty(true);
		$this->assertTrue($tag->isEmpty());
		$tag->setEmpty(false);
		$this->assertFalse($tag->isEmpty());
	}

	public function testAttributes()
	{
		$tag = SgmlOpenTag::create();
		$tag->setAttribute('class', 'active');
		$this->assertCount(1, $tag->getAttributesList());
		$this->assertTrue($tag->hasAttribute('class'));
		$this->assertTrue($tag->hasAttribute('ClasS'));
		$this->assertEquals('active', $tag->getAttribute('class'));
		$this->assertEquals($tag->getAttribute('class'), $tag->getAttribute('CLASS'));

		try {
			$tag->setAttribute('CLASS', 'in-active');
			$this->fail('excepted WrongArgumentException exception');
		} catch (\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		$this->assertCount(1, $tag->getAttributesList());
		$this->assertTrue($tag->hasAttribute('class'));
		$this->assertTrue($tag->hasAttribute('ClasS'));
		$this->assertEquals('active', $tag->getAttribute('class'));
		$this->assertEquals($tag->getAttribute('class'), $tag->getAttribute('CLASS'));

		$tag->setAttribute('data-test', 'in-active');
		$this->assertTrue($tag->hasAttribute('data-test'));
		$this->assertCount(2, $tag->getAttributesList());
		$this->assertEquals('in-active', $tag->getAttribute('data-test'));
		$this->assertEquals(
			['class', 'data-test'],
			array_keys($tag->getAttributesList())
		);
		$this->assertEquals(
			['active', 'in-active'],
			array_values($tag->getAttributesList())
		);

		$tag->dropAttribute('DATA-TEST');
		$this->assertFalse($tag->hasAttribute('data-test'));
		$this->assertCount(1, $tag->getAttributesList());
		try {
			$tag->getAttribute('data-test');
			$this->fail('excepted WrongArgumentException exception');
		} catch (\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			$tag->dropAttribute('data-test');
			$this->fail('excepted WrongArgumentException exception');
		} catch (\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		$tag->setAttribute('data-test', 'in-active');
		$this->assertTrue($tag->hasAttribute('DATA-TEST'));
		$this->assertCount(2, $tag->getAttributesList());
		$this->assertEquals('in-active', $tag->getAttribute('DATA-TEST'));
		$tag->dropAttribute('data-test');
		$this->assertFalse($tag->hasAttribute('data-test'));
		$this->assertCount(1, $tag->getAttributesList());
		$tag->setAttribute('data-test', 'in-active');
		$this->assertCount(2, $tag->getAttributesList());
		$tag->dropAttributesList();
		$this->assertEmpty($tag->getAttributesList());
		try {
			$tag->getAttribute('data-test');
			$this->fail('excepted WrongArgumentException exception');
		} catch (\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		try {
			$tag->getAttribute('class');
			$this->fail('excepted WrongArgumentException exception');
		} catch (\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
	}
}