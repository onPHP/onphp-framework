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

class FunctionBuffer implements Buffer
{
	private $buffer = false;
	private $inFunction = false;
	private $functionName = null;
	/**
	 * @var \Onphp\NsConverter\PenjepitCounter
	 */
	private $penjepitCounter = null;

	/**
	 * @return \Onphp\NsConverter\NamespaceBuffer
	 */
	public function init()
	{
		$this->buffer = false;
		$this->inFunction = false;
		$this->functionName = null;
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
	public function getFunctionName()
	{
		if (!$this->buffer && $this->functionName)
			return $this->functionName;
	}

	public function process($subject, $i)
	{
		if ($this->penjepitCounter) {
			$this->penjepitCounter->process($subject, $i);
		}
		
		if (is_array($subject) && in_array($subject[0], [T_FUNCTION])) {
			$this->buffer = true;
			$this->functionName = '';
			$this->inFunction = true;
			$this->penjepitCounter = null;
		} elseif ($this->buffer) {
			$isBufferEnd = is_string($subject) && in_array($subject, ['(']);

			if (is_array($subject) && in_array($subject[0], [T_STRING])) {
				$this->functionName .= $subject[1];
			} elseif ($isBufferEnd) {
				$this->buffer = false;
			}
		}
		if ($this->inFunction && !$this->penjepitCounter && is_string($subject) && $subject == '{') {
			$this->penjepitCounter = (new PenjepitCounter())->init();
			$this->penjepitCounter->process($subject, $i);
		}
		if ($this->penjepitCounter && !$this->penjepitCounter->isBuffer()) {
			$this->inFunction = false;
			$this->buffer = false;
			$this->functionName = null;
			$this->penjepitCounter = null;
		}
	}
}
