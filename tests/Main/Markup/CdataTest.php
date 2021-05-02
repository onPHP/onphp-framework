<?php

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
}