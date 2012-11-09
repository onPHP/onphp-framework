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

class ChainBuffer implements Buffer
{
	/**
	 * @var \Onphp\NsConverter\ClassBuffer
	 */
	private $buffers = [];

	/**
	 * @param \Onphp\NsConverter\Buffer $buffer
	 * @return \Onphp\NsConverter\ChainBuffer
	 */
	public function addBuffer(Buffer $buffer)
	{
		$this->buffers[] = $buffer;
		return $this;
	}
	
	/**
	 * @return \Onphp\NsConverter\ChainBuffer
	 */
	public function init()
	{
		foreach ($this->buffers as $buffer) {
			/* @var $buffer \Onphp\NsConverter\Buffer */
			$buffer->init();
		}
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
		foreach ($this->buffers as $buffer) {
			/* @var $buffer \Onphp\NsConverter\Buffer */
			$buffer->process($subject, $i);
		}
	}
}
