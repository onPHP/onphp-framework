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
 * To add class names to CodeStorage
 */
class ClassStorageBuffer
{
	private $newNamespace = '';
	/**
	 * @var \Onphp\NsConverter\ClassStorage
	 */
	private $classStorage = null;
	
	private $currentClass = '';
	/**
	 * @var \Onphp\NsConverter\NamespaceBuffer
	 */
	private $namespaceBuffer = null;
	/**
	 * @var \Onphp\NsConverter\ClassBuffer
	 */
	private $classBuffer = null;
	
	/**
	 * @param string $newNamespace
	 * @return \Onphp\NsConverter\ClassStorageBuffer
	 */
	public function setNewNamespace($newNamespace)
	{
		$this->newNamespace = $newNamespace;
		return $this;
	}
	
	/**
	 * @param \Onphp\NsConverter\ClassStorage $classStorage
	 * @return \Onphp\NsConverter\ClassStorageBuffer
	 */
	public function setClassStorage(ClassStorage $classStorage)
	{
		$this->classStorage = $classStorage;
		return $this;
	}

	/**
	 * @param \Onphp\NsConverter\NamespaceBuffer $namespaceBuffer
	 * @return \Onphp\NsConverter\ClassStorageBuffer
	 */
	public function setNamespaceBuffer(NamespaceBuffer $namespaceBuffer)
	{
		$this->namespaceBuffer = $namespaceBuffer;
		return $this;
	}

	/**
	 * @param \Onphp\NsConverter\ClassBuffer $classBuffer
	 * @return \Onphp\NsConverter\ClassStorageBuffer
	 */
	public function setClassBuffer(ClassBuffer $classBuffer)
	{
		$this->classBuffer = $classBuffer;
		return $this;
	}
		
	/**
	 * @return \Onphp\NsConverter\NamespaceBuffer
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