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

use OnPHP\Main\Markup\Html\Cdata;
use OnPHP\Main\Markup\Html\SgmlIgnoredTag;
use OnPHP\Tests\TestEnvironment\TestCase;

class SgmlIgnoredTagTest extends TestCase
{
	public function testCreate()
	{
		$this->assertInstanceOf(SgmlIgnoredTag::class, SgmlIgnoredTag::create());
	}

	public function testComment()
	{
		$tag = SgmlIgnoredTag::comment();

		$this->assertInstanceOf(SgmlIgnoredTag::class, $tag);
		$this->assertEquals('!--', $tag->getId());
		$this->assertEquals('--', $tag->getEndMark());
		$this->assertTrue($tag->isComment());
		$tag->setId('!-');
		$this->assertFalse($tag->isComment());
	}

	public function testCData()
	{
		$tag = SgmlIgnoredTag::comment();

		$this->assertNull($tag->getCdata());
		$tag->setCdata((new Cdata())->setData('test data'));
		$this->assertInstanceOf(Cdata::class, $tag->getCdata());
		$this->assertEquals('test data', $tag->getCdata()->getData());
	}

	public function testEndMark()
	{
		$tag = SgmlIgnoredTag::create();

		$this->assertNull($tag->getEndMark());
		$tag->setEndMark('test-end-mark');
		$this->assertEquals('test-end-mark', $tag->getEndMark());
		$tag->setEndMark('');
		$this->assertEquals('', $tag->getEndMark());
	}

	public function testExternal()
	{
		$tag = SgmlIgnoredTag::create();

		$this->assertFalse($tag->isExternal());
		$tag->setId('!--');
		$this->assertFalse($tag->isExternal());
		$tag->setId('?id');
		$this->assertTrue($tag->isExternal());
		$tag->setId('?');
		$this->assertTrue($tag->isExternal());
		$tag->setId('');
		$this->assertFalse($tag->isExternal());
	}
}