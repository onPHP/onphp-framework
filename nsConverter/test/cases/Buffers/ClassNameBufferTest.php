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

/**
 * @group cn
 */
class ClassNameBufferTest extends TestCase
{
	public function testSimple()
	{
		$file = $this->getTestFileContent();
		$subjects = token_get_all($file);

		$buffer = null;
		$classList = [];
		$prevSubject = null;
		foreach ($subjects as $i => $subject) {
			if (!$buffer && ClassNameBuffer::canStart($subject, $prevSubject)) {
				$buffer = $this->getService();
				$buffer->process($subject, $i);
			} elseif ($buffer) {
				$buffer->process($subject, $i);
				if (!$buffer->isBuffer()) {
					if ($className = $buffer->getClassName()) {
						$classList[] = $buffer->getClassName()
							.':'.$buffer->getClassNameStart()
							.':'.$buffer->getClassNameEnd();
					}
					$buffer = null;
				}
			}
			$prevSubject = $subject;
		}
		
		$expectationClass = ['\NsConverter\Form:3:6', '\onPHP\Model:11:16', 'HttpRequest:23:23'];
		$this->assertEquals(implode("\n", $expectationClass), implode("\n", $classList));
	}

	/**
	 * @return \Onphp\NsConverter\ClassBuffer
	 */
	private function getService()
	{
		return (new ClassNameBuffer())->init();
	}

	private function getTestFileContent()
	{
		return file_get_contents(
				$this->getDataPath('buffers' . DIRECTORY_SEPARATOR . 'ClassNameBufferTest.txt')
		);
	}
}