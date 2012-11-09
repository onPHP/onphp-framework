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

use \Onphp\UnimplementedFeatureException;
use \Onphp\Assert;

class AliasBuffer implements Buffer
{
	/**
	 * @var NamespaceBuffer
	 */
	private $namespaceBuffer = null;
	/**
	 * @var ClassBuffer
	 */
	private $classBuffer = null;
	private $buffer = false;
	private $bufferStart = null;
	private $buffers = [];
	private $aliases = [];
	/**
	 * @var ClassNameBuffer
	 */
	private $classNameBuffer = null;
	private $classFrom = null;
	private $classTo = null;

	/**
	 * @param NamespaceBuffer $namespaceBuffer
	 * @return AliasBuffer
	 */
	public function setNamespaceBuffer(NamespaceBuffer $namespaceBuffer)
	{
		$this->namespaceBuffer = $namespaceBuffer;
		return $this;
	}
	/**
	 * @param ClassBuffer $classBuffer
	 * @return AliasBuffer
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
		$this->buffer = false;
		$this->bufferStart = null;
		$this->buffers = [];
		$this->aliases = [];
		$this->classNameBuffer = null;
		$this->classFrom = null;
		$this->classTo = null;
		return $this;
	}

	public function findClass($className)
	{
		if (isset($this->aliases[$className]))
			return $this->aliases[$className];
	}

	/**
	 * @return bool
	 */
	public function isBuffer()
	{
		return $this->buffer == true;
	}

	public function getAliases()
	{
		return $this->aliases;
	}

	public function getBuffers()
	{
		return $this->buffers;
	}

	public function process($subject, $i)
	{

		if (is_array($subject) && $subject[0] == T_USE && !$this->classBuffer->getClassName()) {
			$this->startBuffer($i);
		} elseif ($this->buffer) {
			if ($this->classNameBuffer) {
				$this->classNameBuffer->process($subject, $i);
			} elseif (ClassNameBuffer::canStart($subject)) {
				$this->classNameBuffer = new ClassNameBuffer();
				$this->classNameBuffer->process($subject, $i);
			}
			if (is_string($subject) && $subject == ';') {
				$this->endAlias();
				$this->endBuffer($i);
			} elseif (is_string($subject) && $subject == ',') {
				$this->endAlias();
			} elseif (is_array($subject) && $subject[0] == T_AS) {
				$this->storeClassName();
			} elseif ($this->classNameBuffer && !$this->classNameBuffer->isBuffer()) {
				throw new UnimplementedFeatureException();
			}
		}
	}

	private function endAlias()
	{
		$this->storeClassName();
		if (!$this->classTo) {
			$fromParts = explode('\\', $this->classFrom);
			$this->classTo = array_pop($fromParts);
		}
		$this->aliases[$this->classTo] = $this->classFrom;
		$this->classFrom = null;
		$this->classTo = null;
	}

	private function startBuffer($i)
	{
		$this->buffer = true;
		$this->classNameBuffer = null;
		$this->bufferStart = $i;
	}

	private function endBuffer($i)
	{
		$this->buffers[] = [$this->bufferStart, $i];

		$this->buffer = false;
		$this->classNameBuffer = null;
		$this->bufferStart = null;
	}

	private function storeClassName()
	{
		Assert::isNotNull($this->classNameBuffer);
		if (!$this->classFrom)
			$this->classFrom = '\\'.ltrim($this->classNameBuffer->getClassName(), '\\');
		elseif (!$this->classTo)
			$this->classTo = trim($this->classNameBuffer->getClassName());
		else
			Assert::isUnreachable ('unreachable');

		$this->classNameBuffer = null;
	}
}
