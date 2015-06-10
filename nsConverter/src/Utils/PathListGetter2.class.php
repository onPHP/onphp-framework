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

use \Onphp\Form;
use \Onphp\Primitive;
use \Onphp\NsConverter\AddUtils\CallbackLogicalObjectSuccess;
use \Onphp\NamespaceResolverPSR0;
use \Onphp\NamespaceResolverOnPHP;
use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;
use \Onphp\NamespaceResolver;

class PathListGetter2
{
	private $ext;
	private $path;
	private $isPsr0;
	private $namespace;

	/**
	 * @param $ext
	 * @return PathListGetter2
	 */
	public function setExt($ext)
	{
		$this->ext = $ext;
		return $this;
	}

	/**
	 * @param $path
	 * @return PathListGetter2
	 */
	public function setPath($path)
	{
		$this->path = $path;
		return $this;
	}

	/**
	 * @param $isPsr0
	 * @return PathListGetter2
	 */
	public function setIsPsr0($isPsr0)
	{
		$this->isPsr0 = ($isPsr0 === true);
		return $this;
	}

	/**
	 * @param $namespace
	 * @return PathListGetter2
	 */
	public function setNamespace($namespace)
	{
		$this->namespace = $namespace;
		return $this;
	}
	
	/**
	 * @param NamespaceResolver $resolver
	 * @return array (path => namespace)
	 */
	public function getPathList()
	{
		$path = realpath($this->path);
		if (is_file($path)) {
			return [$path => NamespaceUtils::fixNamespace($this->namespace)];
		}
		
		$resolver = $this->getNamespaceResolver();
		
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
	private function getNamespaceResolver()
	{
		if ($this->isPsr0) {
			$resolver = NamespaceResolverPSR0::create();
			if ($ext = $this->ext) {
				$resolver->setClassExtension($ext);
			}
			$resolver->setAllowedUnderline(false);
			$resolver->addPath(realpath($this->path), $this->namespace);
			return $resolver;
		}
		
		$resolver = NamespaceResolverOnPHP::create();
		if ($ext = $this->ext) {
			$resolver->setClassExtension($ext);
		}
		
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(realpath($this->path))
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
		
		return $resolver->addPaths($pathList, $this->namespace);
	}
}
