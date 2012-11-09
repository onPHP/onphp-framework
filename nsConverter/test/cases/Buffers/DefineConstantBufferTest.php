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
 * @group dc
 */
class DefineConstantBufferTest extends TestCase
{
	/**
	 * @group dc1
	 */
	public function testSimple()
	{
		$classStorage = $this->execute();

		$exps = [
			'CONST:ABC',
			'CONST:A2BC5',
			'CONST:ab_cf',
		];


		$this->assertEquals(implode("\n", $exps), $classStorage->export());
	}

	/**
	 * @param string $fileNum
	 * @return DefineConstantBuffer
	 */
	private function execute($fileNum = '')
	{
		$classStorage = new ClassStorage();
		$constBuffer = $this->getService($classStorage);

		$file = $this->getTestFileContent($fileNum);
		$subjects = token_get_all($file);

		$constBuffer->init();
		foreach ($subjects as $i => $subject) {
			$constBuffer->process($subject, $i);
		}

		return $classStorage;
	}

	/**
	 * @return \Onphp\NsConverter\DefineConstantBuffer
	 */
	private function getService($classStorage)
	{
		return (new DefineConstantBuffer())
			->setClassStorage($classStorage);
	}

	private function getTestFileContent($fileNum = '')
	{
		return file_get_contents(
				$this->getDataPath('buffers' . DIRECTORY_SEPARATOR . "DefineConstantBufferTest{$fileNum}.txt")
		);
	}
}