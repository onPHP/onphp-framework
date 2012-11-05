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

use \Onphp\Form;
use \Onphp\NamespaceResolver;
use \Onphp\NamespaceResolverOnPHP;
use \Onphp\Primitive;
use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;

trait PathListGetter
{
	/**
	 * @return Form
	 */
	protected function fillFormWithPath(Form $form)
	{
		$form
			->add(Primitive::string('--ext'))
			->add(Primitive::string('--path')->required())
			->add(Primitive::string('--namespace')->setAllowedPattern('~^[\w\d\\\\]+$~iu'))
			->addRule('pathExistsRule', $this->getPathExistsRule('--path'))
		;
	}
	
	protected function getPathExistsRule($property)
	{
		return CallbackLogicalObjectSuccess::create(function(Form $form) use ($property) {
			if ($path = $form->getValue($property)) {
				if (!file_exists($path)) {
					$form->markWrong($property);
				} elseif (!is_readable($path)) {
					$form->markWrong($property);
				}
			}
		});
	}
	
	/**
	 * @param NamespaceResolver $resolver
	 * @return array (path => namespace)
	 */
	protected function getPathList(Form $form)
	{
		$resolver = $this->getNamespaceResolver($form);
		
		$classPathList = $resolver->getClassPathList();
		$pathList = [];
		foreach ($classPathList as $key => $value) {
			if (!is_numeric($key)) {
				list($namespace, $classname) = NamespaceUtils::explodeFullName($key);
				$path = realpath($classPathList[$value])
					.'/'.$classname.$resolver->getClassExtension();
				$pathList[$path] = $namespace;
			}
		}
		return $pathList;
	}
	
	/**
	 * @param Form $form
	 * @return NamespaceResolver
	 */
	private function getNamespaceResolver(Form $form)
	{
		$resolver = NamespaceResolverOnPHP::create();
		if ($ext = $form->getValue('--ext')) {
			$resolver->setClassExtension($ext);
		}
		
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(realpath($form->getValue('--path')))
		);
		$pathList = [];
		foreach ($iterator as $key => $path) {
			if (is_dir($key)) {
				if (preg_match('~\.\.$~', $key)) {
					continue;
				}
				$pathList[] = $key;
			}
		}
		
		return $resolver->addPaths($pathList, $form->getValue('--namespace'));
	}
}
