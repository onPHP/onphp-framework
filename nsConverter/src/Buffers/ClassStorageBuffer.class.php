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

use \Onphp\NsConverter\Utils\ClassStorage;
use \Onphp\NsConverter\Business\NsClass;

/**
 * To add class names to CodeStorage
 */
class ClassStorageBuffer
{
	private $newNamespace = '';
	/**
	 * @var ClassStorage
	 */
	private $classStorage = null;
	
	private $currentClass = '';
	/**
	 * @var NamespaceBuffer
	 */
	private $namespaceBuffer = null;
	/**
	 * @var ClassBuffer
	 */
	private $classBuffer = null;
	
	/**
	 * @param string $newNamespace
	 * @return ClassStorageBuffer
	 */
	public function setNewNamespace($newNamespace)
	{
		$this->newNamespace = $newNamespace;
		return $this;
	}
	
	/**
	 * @param ClassStorage $classStorage
	 * @return ClassStorageBuffer
	 */
	public function setClassStorage(ClassStorage $classStorage)
	{
		$this->classStorage = $classStorage;
		return $this;
	}

	/**
	 * @param NamespaceBuffer $namespaceBuffer
	 * @return ClassStorageBuffer
	 */
	public function setNamespaceBuffer(NamespaceBuffer $namespaceBuffer)
	{
		$this->namespaceBuffer = $namespaceBuffer;
		return $this;
	}

	/**
	 * @param ClassBuffer $classBuffer
	 * @return ClassStorageBuffer
	 */
	public function setClassBuffer(ClassBuffer $classBuffer)
	{
		$this->classBuffer = $classBuffer;
		return $this;
	}
		
	/**
	 * @return NamespaceBuffer
	 */
	public function init()
	{
		$this->currentClass = '';
		$this->namespaceBuffer->init();
		$this->classBuffer->init();
		return $this;
	}

	public function process($subject, $i)
	{
		$this->namespaceBuffer->process($subject, $i);
		$this->classBuffer->process($subject, $i);
		if ($this->classBuffer->getClassName() != $this->currentClass) {
			$this->currentClass = $this->classBuffer->getClassName();
			$this->addClass();
		}
	}
	
	private function addClass()
	{
		if (!$this->currentClass)
			return;
		
		$class = NsClass::create()
			->setName($this->currentClass)
			->setNamespace($this->namespaceBuffer->getNamespace())
			->setNewNamespace($this->newNamespace);
		
		$this->classStorage->addClass($class);
	}
}