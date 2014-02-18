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

use Onphp\NsConverter\Business\ActionEnum;
use Onphp\NsConverter\Utils\NamespaceUtils;
use \Onphp\NsConverter\Utils\OutputMsg;
use \Onphp\NsConverter\Utils\PathListGetter;
use \Onphp\NsConverter\Utils\FormErrorWriter;
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
use Onphp\NsConverter\Utils\PathListGetter2;

class NewReplaceCommand
{
	use OutputMsg;

	public function run(Form $form, ClassStorage $storage)
	{
		foreach ($this->getReplacePaths($form) as $pathData) {
			list($path, $namespace, $isPsr0, $ext, $noAlias) = $pathData;

			$listGetter = (new PathListGetter2())
				->setPath($path)
				->setNamespace($namespace)
				->setIsPsr0($isPsr0)
				->setExt($ext);

			$pathList = $listGetter->getPathList();
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
					->setFilePath($path)
					->setCurrentClassName($className)
					->setNewNamespace($newNamespace)
					->setNamespaceBuffer($namespaceBuffer)
					->setClassStorage($storage)
					->setCodeStorage($codeStorage)
					->setClassNameDetectBuffer($classNameDetectBuffer)
					->setAliasBuffer($aliasBuffer)
					->setSkipUses($noAlias);

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
	}

	private function getReplacePaths(Form $form)
	{
		$pathList = [];
		if ($formList = $form->getValue('pathes')) {
			foreach ($formList as $subForm) {
				if ($path = $this->getReplacePath($subForm)) {
					$pathList[] = $path;
				}
			}
		}
		return $pathList;
	}

	private function getReplacePath(Form $form)
	{
		if ($form->getValue('action')->getId() == ActionEnum::REPLACE) {
			return [
				$form->getValue('path'),
				NamespaceUtils::fixNamespace($form->getValue('namespace')),
				$form->getValue('psr0'),
				$form->getSafeValue('ext'),
				$form->getValue('noAlias'),
			];
		}
	}
}
