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

use \Onphp\Form;
use \Onphp\NsConverter\AddUtils\CMDUtils;
use \Onphp\NsConverter\Buffers\ClassBuffer;
use \Onphp\NsConverter\Buffers\ClassStorageBuffer;
use \Onphp\NsConverter\Buffers\DefineConstantBuffer;
use \Onphp\NsConverter\Buffers\NamespaceBuffer;
use Onphp\NsConverter\Business\ActionEnum;
use \Onphp\NsConverter\Utils\ClassStorage;
use \Onphp\NsConverter\Utils\FormErrorWriter;
use Onphp\NsConverter\Utils\NamespaceUtils;
use \Onphp\NsConverter\Utils\OutputMsg;
use \Onphp\NsConverter\Utils\PathListGetter;
use Onphp\NsConverter\Utils\PathListGetter2;
use \Onphp\Primitive;

class NewScanCommand
{
	use OutputMsg, PathListGetter, FormErrorWriter {
		OutputMsg::msg insteadof FormErrorWriter;
	}

	public function run(Form $form, ClassStorage $classStorage)
	{
		if (!($scanPaths = $this->getScanPaths($form))) {
			$this->msg("no pathes for scan");
		}

		$constantBuffer = (new DefineConstantBuffer())
			->setClassStorage($classStorage);

		$namespaceBuffer = new NamespaceBuffer();
		$classBuffer = new ClassBuffer();
		$buffer = (new ClassStorageBuffer())
			->setClassStorage($classStorage)
			->setNamespaceBuffer($namespaceBuffer)
			->setClassBuffer($classBuffer);

		foreach ($scanPaths as $pathData) {
			list($path, $namespace, $isPsr0, $ext) = $pathData;
			$pathListGetter = (new PathListGetter2())
				->setExt($ext)
				->setIsPsr0($isPsr0)
				->setNamespace($namespace)
				->setPath($path);

			$pathList = $pathListGetter->getPathList();
			foreach ($pathList as $path => $namespace) {
				$subjects = token_get_all(file_get_contents($path));
				$buffer->setNewNamespace($namespace)->init();
				foreach ($subjects as $i => $subject) {
					$buffer->process($subject, $i);
					$constantBuffer->process($subject, $i);
				}
			}
		}
	}

	private function getScanPaths(Form $form)
	{
		$pathList = [];
		if ($formList = $form->getValue('pathes')) {
			foreach ($formList as $subForm) {
				if ($path = $this->getScanPath($subForm)) {
					$pathList[] = $path;
				}
			}
		}
		return $pathList;
	}

	private function getScanPath(Form $form)
	{
		if (in_array($form->getValue('action')->getId(), [ActionEnum::SCAN, ActionEnum::REPLACE])) {
			$this->msg($form->getValue('path'));
			return [
				$form->getValue('path'),
				NamespaceUtils::fixNamespace($form->getValue('namespace')),
				$form->getValue('psr0'),
				$form->getSafeValue('ext'),
			];
		}
	}
}
