<?php
/***************************************************************************
 *   Copyright (C) 2013 by Nikita V. Konstantinov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Tests\Main\Net\Http;

use OnPHP\Core\Exception\MissingElementException;
use OnPHP\Main\Net\Http\HttpHeaderCollection;
use OnPHP\Tests\TestEnvironment\TestCase;

/**
 * @group main
 * @group http
 */
class HttpHeaderCollectionTest extends TestCase
{
	/**
	 * @doesNotPerformAssertions
	 */
	public function testSetter()
	{
		$collection =
			new HttpHeaderCollection(
				array('Content-Length' => 42)
			);

		return $collection;
	}

	/**
	 * @depends testSetter
	 * @doesNotPerformAssertions
	 */
	public function testAddition(HttpHeaderCollection $collection)
	{
		$collection->add('x-foo', 'bar')->add('x-foo', 'baz');

		return $collection;
	}

	/**
	 * @depends testAddition
	 */
	public function testGetter(HttpHeaderCollection $collection)
	{
		$this->assertEquals(array(42), $collection->getRaw('content-LeNgTh'));
		$this->assertEquals(42, $collection->get('content-LeNgTh'));
		$this->assertEquals(array('bar', 'baz'), $collection->getRaw('x-foo'));
		$this->assertEquals('baz', $collection->get('x-foo'));

		return $collection;
	}

	/**
	 * @depends testGetter
	 */
	public function testIterator(HttpHeaderCollection $collection)
	{
		$headerList = array(
			'Content-Length: 42',
			'X-Foo: bar',
			'X-Foo: baz'
		);

		$this->assertEquals($headerList, iterator_to_array($collection));

		return $collection;
	}

	/**
	 * @depends testIterator
	 * @doesNotPerformAssertions
	 */
	public function testRemoving(HttpHeaderCollection $collection)
	{
		$collection->remove('x-foo');

		return $collection;
	}

	/**
	 * @depends testRemoving
	 */
	public function testFailedRemoving(HttpHeaderCollection $collection)
	{
		$this->expectException(MissingElementException::class);
		
		$collection->remove('x-foo');

		return $collection;
	}
}
?>
