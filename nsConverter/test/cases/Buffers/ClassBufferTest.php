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



class ClassBufferTest extends TestCase
{
	public function testSimple()
	{
		$buffer = $this->getService();
		$file = $this->getTestFileContent();
		$subjects = token_get_all($file);

		$expectBuffering = [[6, 9], [24, 27], [91, 94]];
		$expectClassnames = [
			[10, 11, 'TestSecondClassToParse'],
			[28, 79, 'A'],
			[95, 175, 'TestOneClassToParse'],
		];

		foreach ($subjects as $i => $subject) {
			$buffer->process($subject, $i);
			$this->checkBuffering($buffer, $i, $expectBuffering);
			$this->checkNaming($buffer, $i, $expectClassnames);
		}
		$this->assertEquals(178, $i, 'expecting 105 subjects');
	}

	private function checkBuffering(ClassBuffer $buffer, $i, $expectation)
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

	private function checkNaming(ClassBuffer $buffer, $i, $expectation)
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
			$buffer->getClassName(),
			"expecting classname '{$expect}' at token position {$i}"
		);
	}

	/**
	 * @return ClassBuffer
	 */
	private function getService()
	{
		return (new ClassBuffer())->init();
	}

	private function getTestFileContent()
	{
		return file_get_contents(
				$this->getDataPath('buffers' . DIRECTORY_SEPARATOR . 'ClassBufferTest.php')
		);
	}
}