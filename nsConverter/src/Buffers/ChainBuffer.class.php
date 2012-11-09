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

class ChainBuffer implements Buffer
{
	/**
	 * @var ClassBuffer
	 */
	private $buffers = [];

	/**
	 * @param Buffer $buffer
	 * @return ChainBuffer
	 */
	public function addBuffer(Buffer $buffer)
	{
		$this->buffers[] = $buffer;
		return $this;
	}
	
	/**
	 * @return ChainBuffer
	 */
	public function init()
	{
		foreach ($this->buffers as $buffer) {
			/* @var $buffer Buffer */
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
			/* @var $buffer Buffer */
			$buffer->process($subject, $i);
		}
	}
}
