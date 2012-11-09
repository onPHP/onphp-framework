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

namespace Onphp\NsConverter\Utils;

use \Onphp\NsConverter\Test\TestCase as TestCase;
use \Onphp\NsConverter\Buffers\CodeStorage as CodeStorage;
use \Onphp\NsConverter\Buffers\NamespaceBuffer as NamespaceBuffer;
use \Onphp\NsConverter\Buffers\ClassBuffer as ClassBuffer;
use \Onphp\NsConverter\Buffers\AliasBuffer as AliasBuffer;
use \Onphp\NsConverter\Buffers\FunctionBuffer as FunctionBuffer;
use \Onphp\NsConverter\Buffers\ClassNameDetectBuffer as ClassNameDetectBuffer;
use \Onphp\NsConverter\Buffers\ChainBuffer as ChainBuffer;
use \Onphp\NsConverter\Business\NsClass as NsClass;
use \Onphp\NsConverter\Business\NsFunction as NsFunction;
use \Onphp\NsConverter\Business\NsConstant as NsConstant;

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
			['Form', '', 'Onphp'],
			['Model', '', '\\Onphp\\'],
			['HttpRequest', '', 'convert\testclass2'],
			['CommandChain', '', 'Onphp\\'],
			['B', '', 'converter'],
			['View', '', 'Onphp'],
			['Primitive', '', 'Onphp'],
		], [], ['SUPER_CONST', 'CONST', 'lowconst']);

		$codeStorage = $this->runConverter($classStorage, $this->getTestFilePath(), 'convert\testclass2');
		$this->assertEquals(file_get_contents($this->getTestFilePath('1u')), $codeStorage->toString());

		$codeStorage = $this->runConverter($classStorage, $this->getTestFilePath(), 'convert\testclass2', true);
		$this->assertEquals(file_get_contents($this->getTestFilePath('1n')), $codeStorage->toString());
	}

	/**
	 * @group cc2
	 */
	public function testSimpleNs2Ns()
	{
		$classStorage = $this->getClassStorage([
			['Form', 'Onphp', 'Onphp\main'],
			['Model', '\Onphp\\', 'Onphp\core'],
			['HttpRequest', '', 'Onphp\main'],
			['CommandChain', 'Onphp', 'Onphp'],
			['B', 'converter', 'convert\testclass3'],
			['View', 'Onphp', 'convert\testclass3'],
			['Primitive', 'Onphp', 'Onphp'],
		], [], ['SUPER_CONST', 'CONST', 'lowconst']);

		$codeStorage = $this->runConverter($classStorage, $this->getTestFilePath('2'), 'convert\testclass3', true);
		$this->assertEquals(file_get_contents($this->getTestFilePath('2n')), $codeStorage->toString());

		$codeStorage = $this->runConverter($classStorage, $this->getTestFilePath('2'), 'convert\testclass3');
		$this->assertEquals(file_get_contents($this->getTestFilePath('2u')), $codeStorage->toString());
	}

	/**
	 * @group cc3
	 */
	public function testSimplePHtml()
	{
		$classStorage = $this->getClassStorage([
			['DB', '', 'Onphp'],
			['OSQL', '', 'Onphp'],
			['Form', '', 'Onphp'],
		]);

		$codeStorage = $this->runConverter($classStorage, $this->getTestFilePath('3'), 'convert\testclass3');
		$this->assertEquals(file_get_contents($this->getTestFilePath('3u')), $codeStorage->toString());

		$codeStorage = $this->runConverter($classStorage, $this->getTestFilePath('3'), 'convert\testclass3', true);
		$this->assertEquals(file_get_contents($this->getTestFilePath('3n')), $codeStorage->toString());
	}

	/**
	 * @group cc4
	 */
	public function testAliases()
	{
		$classStorage = $this->getClassStorage([
			['Form', 'Onphp', 'Onphp'],
			['Primitive', 'Onphp\Primitives', 'Onphp\Primitives'],
			['OSQL', 'Sub', 'Onphp\OSQL'],
			['OSQL', 'My\Ns', 'My\Ns'],
			['CMDUtils', '\Onphp\Utils', 'OnphpUtils'],
		]);

		$codeStorage = $this->runConverter($classStorage, $this->getTestFilePath('4'), 'My\Ns');
		$this->assertEquals(file_get_contents($this->getTestFilePath('4u')), $codeStorage->toString());

		$codeStorage = $this->runConverter($classStorage, $this->getTestFilePath('4'), 'My\Ns', true);
		$this->assertEquals(file_get_contents($this->getTestFilePath('4n')), $codeStorage->toString());
	}

	/**
	 * @param ClassStorage $storage
	 * @param type $path
	 * @param type $newNamespace
	 * @return CodeStorage
	 */
	private function runConverter(ClassStorage $storage, $path, $newNamespace = null, $skipUses = false)
	{
		$codeStorage = new CodeStorage();
		$namespaceBuffer = new NamespaceBuffer();
		$classBuffer = new ClassBuffer();
		$aliasBuffer = (new AliasBuffer())
			->setClassBuffer($classBuffer);
		$functionBuffer = new FunctionBuffer();
		$classNameDetectBuffer = (new ClassNameDetectBuffer())
			->setNamespaceBuffer($namespaceBuffer)
			->setClassBuffer($classBuffer)
			->setFunctionBuffer($functionBuffer)
			->setAliasBuffer($aliasBuffer);


		$chainBuffer = (new ChainBuffer())
			->addBuffer($codeStorage)
			->addBuffer($namespaceBuffer)
			->addBuffer($classBuffer)
			->addBuffer($aliasBuffer)
			->addBuffer($functionBuffer)
			->addBuffer($classNameDetectBuffer);

		$subjects = token_get_all(file_get_contents($path));

		$chainBuffer->init();
		foreach ($subjects as $i => $subject) {
			$chainBuffer->process($subject, $i);
		}

		$converter = $this->getService();
		$converter
			->setNewNamespace($newNamespace)
			->setNamespaceBuffer($namespaceBuffer)
			->setClassStorage($storage)
			->setCodeStorage($codeStorage)
			->setClassNameDetectBuffer($classNameDetectBuffer)
			->setAliasBuffer($aliasBuffer)
			->setSkipUses($skipUses);

		$converter->run();
		return $codeStorage;
	}

	/**
	 * @param string $classes
	 * @return ClassStorage
	 */
	private function getClassStorage($classes, array $functions = [], array $constants = [])
	{
		$classStorage = new ClassStorage();
		foreach ($classes as $object) {
			list($objectName, $oldNamespace, $newNamespace) = $object;
			$object = NsClass::create()
				->setNamespace($oldNamespace)
				->setNewNamespace($newNamespace)
				->setName($objectName);
			$classStorage->addClass($object);
		}
		foreach ($functions as $object) {
			list($objectName, $oldNamespace, $newNamespace) = $object;
			$object = NsFunction::create()
				->setNamespace($oldNamespace)
				->setNewNamespace($newNamespace)
				->setName($objectName);
			$classStorage->addClass($object);
		}
		foreach ($constants as $constantName) {
			$classStorage->addConstant(NsConstant::create()->setName($constantName));
		}
		return $classStorage;
	}

	/**
	 * @return CodeConverter
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