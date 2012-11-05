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
 * @group cd
 */
class ClassNameDetectBufferTest extends TestCase
{
	public function testSimple()
	{
		$service = $this->getService();

		$service
			->setNamespaceBuffer($nsBuffer = new NamespaceBuffer())
			->setClassBuffer($classBuffer = new ClassBuffer())
			->setFunctionBuffer($functionBuffer = new FunctionBuffer());
		
		$chain = new ChainBuffer();
		$chain
			->addBuffer($nsBuffer)
			->addBuffer($classBuffer)
			->addBuffer($functionBuffer)
			->addBuffer($service);

		$file = $this->getTestFileContent();
		$subjects = token_get_all($file);

		$chain->init();
		foreach ($subjects as $i => $subject) {
			$chain->process($subject, $i);
		}
		
		$expectationClass = [
			'\NsConverter\Form:8:11',
			'\onPHP\Model:16:21',
			'HttpRequest:28:28',
			'\CommandChain:81:82',
		];

		$map = function($value) {return implode(':', $value);};
		$classList = array_map($map, $service->getClassNameList());
		$this->assertEquals(implode("\n", $expectationClass), implode("\n", $classList));
	}

	/**
	 * @return \Onphp\NsConverter\ClassNameDetectBuffer
	 */
	private function getService()
	{
		return (new ClassNameDetectBuffer())->init();
	}

	private function getTestFileContent()
	{
		return file_get_contents(
				$this->getDataPath('buffers' . DIRECTORY_SEPARATOR . 'ClassNameDetectBufferTest.txt')
		);
	}
}