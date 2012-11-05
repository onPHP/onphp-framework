<?php

/* * *************************************************************************
 *   Copyright (C) 2012 by Alexey Denisov                                  *
 *   alexeydsov@gmail.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 * ************************************************************************* */

namespace Onphp\NsConverter;

class NamespaceBufferTest extends TestCase
{

	public function testSimple()
	{
		$buffer = $this->getService();
		$file = $this->getTestFileContent();
		$subjects = token_get_all($file);

		$expectBuffering = [[2, 6], [45, 46], [59, 64]];
		$expectNamespaces = [
			[7, 44, 'converter\testclass'],
			[65, 102, 'convert\testclass2'],
		];

		foreach ($subjects as $i => $subject) {
			$buffer->process($subject, $i);
			$this->checkBuffering($buffer, $i, $expectBuffering);
			$this->checkNamespacing($buffer, $i, $expectNamespaces);
		}
		$this->assertEquals(59, $buffer->getBufferStart());
		$this->assertEquals(65, $buffer->getBufferEnd());
		$this->assertEquals(105, $i, 'expecting 105 subjects');
	}

	private function checkBuffering(NamespaceBuffer $buffer, $i, $expectation)
	{
		$expect = false;
		foreach ($expectation as $minMax) {
			list($min, $max) = $minMax;
			if ($i >= $min && $i <= $max) {
				$expect = true;
				break;
			}
		}
		
		if ($expect)
			$this->assertTrue($buffer->isBuffer(), "expecting buffer at {$i} token position");
		else
			$this->assertFalse($buffer->isBuffer(), "not expected buffer at {$i} token position");
	}

	private function checkNamespacing(NamespaceBuffer $buffer, $i, $expectation)
	{
		$expect = '';
		foreach ($expectation as $minMax) {
			list($min, $max, $namespace) = $minMax;
			if ($i >= $min && $i <= $max) {
				$expect = $namespace;
				break;
			}
		}
		
		$this->assertEquals(
			$expect,
			$buffer->getNamespace(),
			"expecting namespace '{$expect}' at token position {$i}"
		);
	}

	/**
	 * @return \Onphp\NsConverter\NamespaceBuffer
	 */
	private function getService()
	{
		return (new NamespaceBuffer())->init();
	}

	private function getTestFileContent()
	{
		return file_get_contents(
				$this->getDataPath('buffers' . DIRECTORY_SEPARATOR . 'NamespaceBufferTest.php')
		);
	}
}