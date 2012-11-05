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

class AliasBuffer implements Buffer
{
	/**
	 * @var \Onphp\NsConverter\NamespaceBuffer
	 */
	private $namespaceBuffer = null;
	/**
	 * @var \Onphp\NsConverter\ClassBuffer
	 */
	private $classBuffer = null;
	private $buffer = false;
	private $aliases = [];
	/**
	 * @var \Onphp\NsConverter\ClassNameBuffer
	 */
	private $classNameBuffer = null;
	
	/**
	 * @param \Onphp\NsConverter\NamespaceBuffer $namespaceBuffer
	 * @return \Onphp\NsConverter\AliasBuffer
	 */
	public function setNamespaceBuffer(NamespaceBuffer $namespaceBuffer)
	{
		$this->namespaceBuffer = $namespaceBuffer;
		return $this;
	}
	/**
	 * @param \Onphp\NsConverter\ClassBuffer $classBuffer
	 * @return \Onphp\NsConverter\AliasBuffer
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
		$this->buffer = false;
		$this->aliases = [];
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isBuffer()
	{
		return $this->buffer == true;
	}
	
	public function getRealClassName($className)
	{
		return $className;
	}

	public function process($subject, $i)
	{
		if ($this->classNameBuffer) {
			$this->classNameBuffer->process($subject, $i);
		}
		
		if (is_array($subject) && $subject[0] == T_USE && !$this->classBuffer->isBuffer()) {
			$this->buffer = true;
			$this->classNameBuffer = null;
		} elseif ($this->buffer) {
			if ($this->classNameBuffer) {
				$this->classNameBuffer->process($subject, $i);
			} elseif (ClassNameBuffer::canStart($subject)) {
				$this->classNameBuffer = new ClassNameBuffer();
				$this->classNameBuffer->process($subject, $i);
			}
			if (is_string($subject) && $subject == ';') {
				$this->buffer = false;
			} /*elseif ($subject)*/
		}
	}
}
