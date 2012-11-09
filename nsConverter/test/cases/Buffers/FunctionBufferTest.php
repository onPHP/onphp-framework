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

namespace Onphp\NsConverter\Buffers;

use \Onphp\NsConverter\Test\TestCase;

/**
 * @group fb
 */
class FunctionBufferTest extends TestCase
{
	public function testSimple()
	{
		$buffer = $this->getService();
		$file = $this->getTestFileContent();
		$subjects = token_get_all($file);

		$expectBuffering = [[17, 19], [71, 72]];
		$expectClassnames = [
			[20, 40, 'doSomething'],
			[73, 90, ''],
		];

		foreach ($subjects as $i => $subject) {
			$buffer->process($subject, $i);
			$this->checkBuffering($buffer, $i, $expectBuffering);
			$this->checkNaming($buffer, $i, $expectClassnames);
		}
		$this->assertEquals(105, $i, 'expecting 105 subjects');
	}

	private function checkBuffering(FunctionBuffer $buffer, $i, $expectation)
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

	private function checkNaming(FunctionBuffer $buffer, $i, $expectation)
	{
		$expect = '';
		foreach ($expectation as $minMax) {
			list($min, $max, $classname) = $minMax;
			if ($i >= $min && $i <= $max) {
				$expect = $classname;
				break;
			}
		}
		
		$this->assertEquals(
			$expect,
			$buffer->getFunctionName(),
			"expecting classname '{$expect}' at token position {$i}"
		);
	}

	/**
	 * @return FunctionBuffer
	 */
	private function getService()
	{
		return (new FunctionBuffer())->init();
	}

	private function getTestFileContent()
	{
		return file_get_contents(
			$this->getDataPath('buffers' . DIRECTORY_SEPARATOR . 'FunctionBufferTest.php')
		);
	}
}