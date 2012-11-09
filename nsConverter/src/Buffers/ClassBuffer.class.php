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

class ClassBuffer implements Buffer
{
	private $buffer = false;
	private $inClass = false;
	private $className = null;
	/**
	 * @var PenjepitCounter
	 */
	private $penjepitCounter = null;

	/**
	 * @return NamespaceBuffer
	 */
	public function init()
	{
		$this->buffer = false;
		$this->inClass = false;
		$this->className = null;
		$this->penjepitCounter = null;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isBuffer()
	{
		return $this->buffer == true;
	}
	
	/**
	 * @return string
	 */
	public function getClassName()
	{
		if (!$this->buffer && $this->className)
			return $this->className;
	}

	public function process($subject, $i)
	{
		if ($this->penjepitCounter) {
			$this->penjepitCounter->process($subject, $i);
		}
		
		if (is_array($subject) && in_array($subject[0], [T_CLASS, T_INTERFACE, T_TRAIT])) {
			$this->buffer = true;
			$this->className = '';
			$this->inClass = true;
			$this->penjepitCounter = null;
		} elseif ($this->buffer) {
			$isBufferEnd1 = is_array($subject) && in_array($subject[0], [T_EXTENDS, T_IMPLEMENTS]);
			$isBufferEnd2 = is_string($subject) && in_array($subject, ['{', ';', '(']);

			if (is_array($subject) && in_array($subject[0], [T_STRING])) {
				$this->className .= $subject[1];
			} elseif ($isBufferEnd1 || $isBufferEnd2) {
				$this->buffer = false;
			}
		}
		if ($this->inClass && !$this->penjepitCounter && is_string($subject) && $subject == '{') {
			$this->penjepitCounter = (new PenjepitCounter())->init();
			$this->penjepitCounter->process($subject, $i);
		}
		if ($this->penjepitCounter && !$this->penjepitCounter->isBuffer()) {
			$this->inClass = false;
			$this->buffer = false;
			$this->className = null;
			$this->penjepitCounter = null;
		}
	}
}
