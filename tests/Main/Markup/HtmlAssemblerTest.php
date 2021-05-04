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
use OnPHP\Main\Markup\Html\Cdata;
use OnPHP\Main\Markup\Html\HtmlAssembler;
use OnPHP\Main\Markup\Html\SgmlEndTag;
use OnPHP\Main\Markup\Html\SgmlIgnoredTag;
use OnPHP\Main\Markup\Html\SgmlOpenTag;
use OnPHP\Tests\TestEnvironment\SgmlTag;
use OnPHP\Tests\TestEnvironment\TestCase;

class HtmlAssemblerTest extends TestCase
{
	public function testConstructor()
	{
		$html = new HtmlAssembler([]);
		$this->assertNull($html->getHtml());
		$this->assertEmpty($this->getObjectProperty($html, 'tags'));

		$input = [
			SgmlOpenTag::create()->setId('div'),
			Cdata::create()->setData('test text'),
			SgmlEndTag::create()->setId('div')
		];
		$html = new HtmlAssembler($input);
		$tags = $this->getObjectProperty($html, 'tags');
		$this->assertCount(3, $tags);
		$this->assertEquals($input, $tags);

		$input[] = 'test string';
		$this->expectException(WrongArgumentException::class);
		new HtmlAssembler($input);
	}

	public function testMakeTag()
	{
		$cdata = Cdata::create()->setData('test-data');
		$this->assertEquals($cdata->getData(), HtmlAssembler::makeTag($cdata));
		$cdata->setStrict(true);
		$this->assertEquals($cdata->getData(), HtmlAssembler::makeTag($cdata));

		try {
			HtmlAssembler::makeTag(SgmlIgnoredTag::create());
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		$this->assertEquals(
			'<!---->',
			HtmlAssembler::makeTag(SgmlIgnoredTag::comment())
		);
		$this->assertEquals(
			'<!--comment-->',
			HtmlAssembler::makeTag(
				SgmlIgnoredTag::comment()->setCdata(Cdata::create()->setData('comment'))
			)
		);
		$cdata = Cdata::create()->setData('comment')->setStrict(true);
		$this->assertEquals(
			'<!--'.$cdata->getData().'-->',
			HtmlAssembler::makeTag(
				SgmlIgnoredTag::comment()->setCdata($cdata)
			)
		);
		$cdata = Cdata::create()->setData(' /* PHP code */ ');
		$this->assertEquals(
			'<?php'.$cdata->getData().'?>',
			HtmlAssembler::makeTag(
				SgmlIgnoredTag::create()->setId('?php')->setEndMark('?')->setCdata($cdata)
			)
		);

		try {
			HtmlAssembler::makeTag(SgmlOpenTag::create());
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		$this->assertEquals(
			'<div>',
			HtmlAssembler::makeTag(SgmlOpenTag::create()->setId('div'))
		);
		$this->assertEquals(
			'<div/>',
			HtmlAssembler::makeTag(SgmlOpenTag::create()->setId('div')->setEmpty(true))
		);
		$this->assertEquals(
			'<div class="test"/>',
			HtmlAssembler::makeTag(
				SgmlOpenTag::create()->setId('div')->setEmpty(true)->setAttribute('class', 'test')
			)
		);
		$this->assertEquals(
			'<div class="test" required/>',
			HtmlAssembler::makeTag(
				SgmlOpenTag::create()
					->setId('div')->setEmpty(true)
					->setAttribute('class', 'test')
					->setAttribute('required')
			)
		);

		try {
			HtmlAssembler::makeTag(SgmlEndTag::create());
			$this->fail('expected WrongArgumentException exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}
		$this->assertEquals(
			'</div>',
			HtmlAssembler::makeTag(SgmlEndTag::create()->setId('div'))
		);

		$this->expectException(WrongArgumentException::class);
		HtmlAssembler::makeTag(SgmlTag::create());
	}

	public function testGetAttributes()
	{
		$this->assertEmpty(
			$this->callObjectMethod(
				HtmlAssembler::class,
				'getAttributes',
				SgmlOpenTag::create()->setId('div')
			)
		);

		$this->assertEquals(
			'data-test',
			$this->callObjectMethod(
				HtmlAssembler::class,
				'getAttributes',
				SgmlOpenTag::create()->setId('div')->setAttribute('data-test')
			)
		);

		$this->assertEquals(
			'class="test"',
			$this->callObjectMethod(
				HtmlAssembler::class,
				'getAttributes',
				SgmlOpenTag::create()->setId('div')->setAttribute('class', 'test')
			)
		);

		$this->assertEquals(
			'class="test" data-test',
			$this->callObjectMethod(
				HtmlAssembler::class,
				'getAttributes',
				SgmlOpenTag::create()->setId('div')
					->setAttribute('class', 'test')
					->setAttribute('data-test')
			)
		);

		$this->assertEquals(
			'class="test" data-test id="&quot;id"',
			$this->callObjectMethod(
				HtmlAssembler::class,
				'getAttributes',
				SgmlOpenTag::create()->setId('div')
					->setAttribute('class', 'test')
					->setAttribute('data-test')
					->setAttribute('id', '"id')
			)
		);
	}

	public function testGetDomAttributes()
	{
		$doc = new \DOMDocument("1.0");
		$node = $doc->appendChild($doc->createElement("div"));

		$this->assertEmpty(
			$this->callObjectMethod(
				HtmlAssembler::class,
				'getDomAttributes',
				$node
			)
		);

		$node->setAttribute("data-test", null);
		$this->assertEquals(
			'data-test',
			$this->callObjectMethod(
				HtmlAssembler::class,
				'getDomAttributes',
				$node
			)
		);

		$node->setAttribute('class', 'test');
		$this->assertEquals(
			'data-test class="test"',
			$this->callObjectMethod(
				HtmlAssembler::class,
				'getDomAttributes',
				$node
			)
		);

		$node->setAttribute('id', '"id');
		$this->assertEquals(
			'data-test class="test" id="&quot;id"',
			$this->callObjectMethod(
				HtmlAssembler::class,
				'getDomAttributes',
				$node
			)
		);
	}
}