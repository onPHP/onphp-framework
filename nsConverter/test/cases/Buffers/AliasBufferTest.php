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
 * @group ab
 */
class AliasBufferTest extends TestCase
{
	/**
	 * @group ab1
	 */
	public function testSimple()
	{
		$aliasBuffer = $this->execute();
		
		$this->assertEquals(['Class1' => '\SomeNs\Sub\Class1'], $aliasBuffer->getAliases());
		$this->assertEquals([[6, 18]], $aliasBuffer->getBuffers());
	}
	
	/**
	 * @group ab2
	 */
	public function testMultiple()
	{
		$aliasExp = [
			'Class1' => '\SomeNs\Sub\Class1',
			'\SomeNs2\Sub3' => '\SomeNs\Sub2',
			'Exception' => '\Exception',
		];
		
		$aliasBuffer = $this->execute('2');
		
		$this->assertEquals($aliasExp, $aliasBuffer->getAliases());
		$this->assertEquals([[6, 35]], $aliasBuffer->getBuffers());
	}
	
	/**
	 * @group ab3
	 */
	public function testMixed()
	{
		$aliasExp = [
			'Class1' => '\SomeNs\Sub\Class1',
			'\SomeNs2\Sub3' => '\SomeNs\Sub2',
			'Exception' => '\Exception',
			'Exception2' => '\Exception2',
			'Class2' => '\SomeNs\Sub\Class1',
		];
		$buffersExp = [
			[6, 35],
			[37, 41],
			[43, 55],
		];
		
		$aliasBuffer = $this->execute('3');
		
		$this->assertEquals($aliasExp, $aliasBuffer->getAliases());
		$this->assertEquals($buffersExp, $aliasBuffer->getBuffers());
	}
	
	/**
	 * @param string $fileNum
	 * @return AliasBuffer
	 */
	private function execute($fileNum = '')
	{
		$chain = new ChainBuffer();
		$chain->addBuffer($nsBuffer = new NamespaceBuffer());
		$chain->addBuffer($classBuffer = new ClassBuffer());
		$chain->addBuffer($aliasBuffer = $this->getService($nsBuffer, $classBuffer));
		
		$file = $this->getTestFileContent($fileNum);
		$subjects = token_get_all($file);

		$chain->init();
		foreach ($subjects as $i => $subject) {
			$chain->process($subject, $i);
		}
		
		return $aliasBuffer;
	}

	/**
	 * @return AliasBuffer
	 */
	private function getService(NamespaceBuffer $nsBuffer, ClassBuffer $classBuffer)
	{
		return (new AliasBuffer())
			->setNamespaceBuffer($nsBuffer)
			->setClassBuffer($classBuffer)
			;
	}

	private function getTestFileContent($fileNum = '')
	{
		return file_get_contents(
				$this->getDataPath('buffers' . DIRECTORY_SEPARATOR . "AliasBufferTest{$fileNum}.txt")
		);
	}
}