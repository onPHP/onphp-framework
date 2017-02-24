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

class ClassNameDetectBuffer implements Buffer
{
	/**
	 * @var NamespaceBuffer
	 */
	private $namespaceBuffer = null;
	/**
	 * @var ClassBuffer
	 */
	private $classBuffer = null;
	/**
	 * @var FunctionBuffer
	 */
	private $functionBuffer = null;
	/**
	 * @var AliasBuffer
	 */
	private $aliasBuffer = null;

	/**
	 * @var ClassNameBuffer
	 */
	private $classNameBuffer = null;
	private $classNameList = [];
	private $prevSubject = null;
	private $lineNum = 0;

	/**
	 * @param NamespaceBuffer $namespaceBuffer
	 * @return ClassNameDetectBuffer
	 */
	public function setNamespaceBuffer(NamespaceBuffer $namespaceBuffer)
	{
		$this->namespaceBuffer = $namespaceBuffer;
		return $this;
	}

	/**
	 * @param ClassBuffer $classBuffer
	 * @return ClassNameDetectBuffer
	 */
	public function setClassBuffer(ClassBuffer $classBuffer)
	{
		$this->classBuffer = $classBuffer;
		return $this;
	}

	/**
	 * @param FunctionBuffer $functionBuffer
	 * @return ClassNameDetectBuffer
	 */
	public function setFunctionBuffer(FunctionBuffer $functionBuffer)
	{
		$this->functionBuffer = $functionBuffer;
		return $this;
	}

	public function setAliasBuffer(AliasBuffer $aliasBuffer)
	{
		$this->aliasBuffer = $aliasBuffer;
		return $this;
	}

	public function getClassNameList()
	{
		return $this->classNameList;
	}

	/**
	 * @return NamespaceBuffer
	 */
	public function init()
	{
		$this->classNameBuffer = null;
		$this->classNameList = [];
		$this->prevSubject = null;
		$this->lineNum = 0;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isBuffer()
	{
		return false;
	}

	public function process($subject, $i)
	{
		if (is_array($subject) && count($subject) >= 3) {
			$this->lineNum = $subject[2];
		}
		if ($this->classNameBuffer) {
			$this->classNameBuffer->process($subject, $i);
			if (!$this->classNameBuffer->isBuffer()) {
				if ($this->classNameBuffer->getClassName()) {
					$this->classNameList[] = [
						$this->classNameBuffer->getClassName(),
						$this->classNameBuffer->getClassNameStart(),
						$this->classNameBuffer->getClassNameEnd(),
						$this->lineNum
					];
				}
				$this->classNameBuffer = null;
			}
		} elseif (
			!$this->isSubBuffered()
			&& ClassNameBuffer::canStart($subject, $this->prevSubject)
		) {
			$this->classNameBuffer = new ClassNameBuffer();
			$this->classNameBuffer->process($subject, $i);
		}

		$this->pushSubject($subject);
	}

	private function isSubBuffered()
	{
		return $this->namespaceBuffer->isBuffer()
			|| $this->classBuffer->isBuffer()
			|| $this->functionBuffer->isBuffer()
			|| $this->aliasBuffer->isBuffer();
	}

	private function pushSubject($subject)
	{
		$isSkip = is_array($subject) && in_array($subject[0], [T_WHITESPACE]);
		if (!$isSkip) {
			$this->prevSubject = $subject;
		}
	}
}
