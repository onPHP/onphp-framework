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



class CodeStorageTest extends TestCase
{

	public function testSimpleCopy()
	{
		$storage = $this->getService();
		$file = $this->getTestFileContent();
		$subjects = token_get_all($file);
		foreach ($subjects as $i => $subject) {
			$storage->process($subject, $i);
		}
		$this->assertEquals($file, $storage->toString());
	}
	
	public function testOneSizeReplace()
	{
		$storage = $this->getService();
		$file = $this->getTestFileContent();
		$subjects = token_get_all($file);
		foreach ($subjects as $i => $subject) {
			$storage->process($subject, $i);
		}
		$storage
			->addReplace('<?', 0)
			->addReplace('', count($subjects) - 1);
		
		$expectation = preg_replace(["~^<\?php\n~u", '~\?>$~u'], ['<?', ''], $file);
		$this->assertEquals($expectation, $storage->toString());
	}
	
	public function testMoreThanOneSizeReplace()
	{
		$storage = $this->getService();
		$file = $this->getTestFileContent();
		$subjects = token_get_all($file);
		foreach ($subjects as $i => $subject) {
			$storage->process($subject, $i);
		}
		$storage
			->addReplace('require "someThing"', 2, 7);
		
		$expectation = preg_replace(["~namespace[^;]+;~iu"], ['require "someThing"'], $file);
		$this->assertEquals($expectation, $storage->toString());
	}
	
	public function testAppend()
	{
		$storage = $this->getService();
		$file = $this->getTestFileContent();
		$subjects = token_get_all($file);
		foreach ($subjects as $i => $subject) {
			$storage->process($subject, $i);
		}
		$storage->addAppend(' extends SomeAnotherClass', 11);
		$expectation = preg_replace(["~(TestOneClassToParse)~iu"], ['$1 extends SomeAnotherClass'], $file);
		$this->assertEquals($expectation, $storage->toString());
	}

	/**
	 * @return CodeStorage
	 */
	private function getService()
	{
		return (new CodeStorage())->init();
	}
	
	private function getTestFileContent()
	{
		return file_get_contents(
			$this->getDataPath('buffers' . DIRECTORY_SEPARATOR . 'CodeStorageTest.php')
		);
	}
}