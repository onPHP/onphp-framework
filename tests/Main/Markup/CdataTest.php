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
use OnPHP\Tests\TestEnvironment\TestCase;

class CdataTest extends TestCase
{
	public function testCreate()
	{
		$tag = Cdata::create();
		$this->assertInstanceOf(Cdata::class, $tag);
	}

	public function testStrict()
	{
		$tag = Cdata::create();
		$this->assertFalse($tag->isStrict());

		$this->assertInstanceOf(Cdata::class, $tag->setStrict(true));
		$this->assertTrue($tag->isStrict());
	}

	public function testData()
	{
		$tag = Cdata::create();
		$this->assertNull($tag->getData());
		$this->assertNull($tag->getRawData());

		$data = 'test data content';
		$tag->setData($data);
		$this->assertEquals($data, $tag->getRawData());
		$this->assertEquals($data, $tag->getData());
		$tag->setStrict(true);
		$this->assertEquals($data, $tag->getRawData());
		$this->assertEquals(
			Cdata::CDATA_STRICT_START . $data . Cdata::CDATA_STRICT_END,
			$tag->getData()
		);
	}
}