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
 * @group cc
 */
class CodeConverterTest extends TestCase
{
	/**
	 * @group cc1
	 */
	public function testSimpleNoNamespace()
	{
		$classStorage = $this->getClassStorage([
			['\Onphp\Form', '', 'Onphp'],
			['\Onphp\Model', '', '\\Onphp\\'],
			['\Onphp\HttpRequest', '', 'convert\testclass2'],
			['\Onphp\CommandChain', '', 'Onphp\\'],
			['B', '', 'converter'],
			['\Onphp\View', '', 'Onphp'],
			['\Onphp\Primitive', '', 'Onphp'],
		]);
		
		$codeStorage = $this->runConverter($classStorage, $this->getTestFilePath(), 'convert\testclass2');
		$this->assertEquals(file_get_contents($this->getTestFilePath('2')), $codeStorage->toString());
	}

	/**
	 * @group cc2
	 */
	public function testSimpleNs2Ns()
	{
		$classStorage = $this->getClassStorage([
			['\Onphp\Form', 'Onphp', 'Onphp\main'],
			['\Onphp\Model', '\\Onphp\\', 'Onphp\core'],
			['\Onphp\HttpRequest', '', 'Onphp\main'],
			['\Onphp\CommandChain', 'Onphp', 'Onphp'],
			['B', 'converter', 'convert\testclass3'],
			['\Onphp\View', 'Onphp', 'convert\testclass3'],
			['\Onphp\Primitive', 'Onphp', 'Onphp'],
		]);

		$codeStorage = $this->runConverter($classStorage, $this->getTestFilePath('2'), 'convert\testclass3');
		file_put_contents('result.txt', $codeStorage->toString());
		$this->assertEquals(file_get_contents($this->getTestFilePath('3')), $codeStorage->toString());
	}

	/**
	 * @group cc3
	 */
	public function testSimplePHtml()
	{
		$classStorage = $this->getClassStorage([
			['\Onphp\DB', '', 'Onphp'],
			['\Onphp\OSQL', '', 'Onphp'],
			['\Onphp\Form', '', 'Onphp'],
		]);

		$codeStorage = $this->runConverter($classStorage, $this->getTestFilePath('4'), 'convert\testclass3');
		file_put_contents('result.txt', $codeStorage->toString());
		$this->assertEquals(file_get_contents($this->getTestFilePath('4r')), $codeStorage->toString());
	}

	/**
	 * @param \Onphp\NsConverter\ClassStorage $storage
	 * @param type $path
	 * @param type $newNamespace
	 * @return \Onphp\NsConverter\CodeStorage
	 */
	private function runConverter(ClassStorage $storage, $path, $newNamespace = null)
	{
		$codeStorage = new CodeStorage();
		$namespaceBuffer = new NamespaceBuffer();
		$classBuffer = new ClassBuffer();
		$functionBuffer = new FunctionBuffer();
		$classNameDetectBuffer = (new ClassNameDetectBuffer())
			->setNamespaceBuffer($namespaceBuffer)
			->setClassBuffer($classBuffer)
			->setFunctionBuffer($functionBuffer);

		$chainBuffer = (new ChainBuffer())
			->addBuffer($codeStorage)
			->addBuffer($namespaceBuffer)
			->addBuffer($classBuffer)
			->addBuffer($functionBuffer)
			->addBuffer($classNameDetectBuffer);

		$subjects = token_get_all(file_get_contents($path));

		$chainBuffer->init();
		foreach ($subjects as $i => $subject) {
			$chainBuffer->process($subject, $i);
		}

		$converter = new CodeConverter();
		$converter
			->setNewNamespace($newNamespace)
			->setNamespaceBuffer($namespaceBuffer)
			->setClassStorage($storage)
			->setCodeStorage($codeStorage)
			->setClassNameDetectBuffer($classNameDetectBuffer);

		$converter->run();
		return $codeStorage;
	}

	/**
	 * @param string $classes
	 * @return \Onphp\NsConverter\ClassStorage
	 */
	private function getClassStorage($classes)
	{
		$classStorage = new ClassStorage();
		foreach ($classes as $class) {
			list($className, $oldNamespace, $newNamespace) = $class;
			$class = NsClass::create()
				->setNamespace($oldNamespace)
				->setNewNamespace($newNamespace)
				->setName($className);
			$classStorage->addClass($class);
		}
		return $classStorage;
	}

	/**
	 * @return \Onphp\NsConverter\CodeConverter
	 */
	private function getService()
	{
		return (new CodeConverter());
	}

	private function getTestFilePath($num = '1')
	{
		return $this->getDataPath('Utils' . DIRECTORY_SEPARATOR . "CodeConverterTest{$num}.txt");
	}
}