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

namespace Onphp\NsConverter\Flow;

use \Onphp\NsConverter\Utils\OutputMsg;
use \Onphp\NsConverter\Utils\PathListGetter;
use \Onphp\NsConverter\Utils\FormErrorWriter;
use \Onphp\NsConverter\AddUtils\CMDUtils;
use \Onphp\Form;
use \Onphp\NsConverter\Utils\ClassStorage;
use \Onphp\NsConverter\Buffers\CodeStorage;
use \Onphp\NsConverter\Buffers\NamespaceBuffer;
use \Onphp\NsConverter\Buffers\ClassBuffer;
use \Onphp\NsConverter\Buffers\AliasBuffer;
use \Onphp\NsConverter\Buffers\FunctionBuffer;
use \Onphp\NsConverter\Buffers\ClassNameDetectBuffer;
use \Onphp\NsConverter\Buffers\ChainBuffer;
use \Onphp\ClassUtils;
use \Onphp\NsConverter\Utils\CodeConverter;
use \Exception;
use \Onphp\NsConverter\Utils\CodeConverterException;
use \RecursiveDirectoryIterator;
use \Onphp\Primitive;

class ReplaceCommand
{
	use OutputMsg, PathListGetter, FormErrorWriter {
		OutputMsg::msg insteadof FormErrorWriter;
	}

	public function run()
	{
		$form = $this->getForm()
			->import(CMDUtils::getOptionsList())
			->checkRules();

		if ($this->processFormError($form)) {
			return;
		}

		$storage = $this->loadConfig($form);
		$pathList = $this->getPathList($form);
		$this->replace($form, $storage, $pathList);
	}

	private function replace(Form $form, ClassStorage $storage, array $pathList)
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

		foreach ($pathList as $path => $newNamespace) {
			$subjects = token_get_all(file_get_contents($path));

			$chainBuffer->init();
			$className = null;
			foreach ($subjects as $i => $subject) {
				$chainBuffer->process($subject, $i);
				if ($className == null && $classBuffer->getClassName()) {
					$className = ClassUtils::normalClassName(
						trim($newNamespace, '\\').'\\'.$classBuffer->getClassName()
					);
				}
			}

			$converter = new CodeConverter();
			$converter
				->setCurrentClassName($className)
				->setNewNamespace($newNamespace)
				->setNamespaceBuffer($namespaceBuffer)
				->setClassStorage($storage)
				->setCodeStorage($codeStorage)
				->setClassNameDetectBuffer($classNameDetectBuffer)
				->setAliasBuffer($aliasBuffer)
				->setSkipUses($form->getValue('--noAlias'));

			try {
				$converter->run();
			} catch (Exception $e) {
				throw new CodeConverterException(
					'Exception while file ('.$path.') converting: '.
						print_r([get_class($e), $e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e->getTraceAsString()], true),
					null,
					$e
				);
			}

			file_put_contents($path, $codeStorage->toString());
		}
	}

	/**
	 *
	 * @param Form $form
	 * @return ClassStorage
	 */
	private function loadConfig(Form $form)
	{
		$storage = new ClassStorage();
		$path = $form->getValue('--config');
		if (is_file($path)) {
			$storage->import(file_get_contents($path));
		} elseif (is_dir($path)) {
			$iterator = new RecursiveDirectoryIterator($path);
			foreach ($iterator as $key => $value) {
				if (is_file($key)) {
					$storage->import(file_get_contents($key));
				}
			}
		}
		return $storage;
	}

	/**
	 * @return Form
	 */
	private function getForm()
	{
		$form = Form::create()
			->add(Primitive::string('--config')->required())
			->add($noAlias = Primitive::boolean('--noAlias'))
			->add(Primitive::alias('--skipAlias', $noAlias))
			->addRule('configExistsRule', $this->getPathExistsRule('--config'));
		$this->fillFormWithPath($form);
		return $form;
	}
}
