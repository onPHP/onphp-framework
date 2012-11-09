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

class NamespaceBuffer implements Buffer
{
	private $buffer = null;
	private $bufferStart = null;
	private $bufferEnd = null;
	private $namespace = '';
	/**
	 * @var PenjepitCounter
	 */
	private $penjepitCounter = null;

	/**
	 * @return NamespaceBuffer
	 */
	public function init()
	{
		$this->buffer = null;
		$this->bufferStart = null;
		$this->bufferEnd = null;
		$this->namespace = '';
		$this->penjepitCounter = null;
		return $this;
	}

	public function isBuffer()
	{
		return $this->buffer !== null;
	}
	
	public function getNamespace()
	{
		return $this->buffer ? '' : $this->namespace;
	}
	
	public function getBufferStart()
	{
		return $this->bufferStart;
	}
	
	public function getBufferEnd()
	{
		return $this->bufferEnd;
	}

	public function process($subject, $i)
	{
		if ($this->penjepitCounter) {
			$this->penjepitCounter->process($subject, $i);
		}
		if (is_array($subject) && $subject[0] == T_NAMESPACE) {
			$this->penjepitCounter = null;
			$this->buffer = $i;
			$this->bufferStart = $i;
			$this->namespace = '';
		} elseif (!is_null($this->buffer)) {
			if (is_array($subject) && in_array($subject[0], [T_STRING, T_NS_SEPARATOR])) {
				$this->namespace .= $subject[1];
			} elseif (is_string($subject) && in_array($subject, [';', '{'])) {
				$this->bufferEnd = $i;
				$this->buffer = null;
				if ($subject == '{') {
					$this->penjepitCounter = (new PenjepitCounter())->init();
					$this->penjepitCounter->process($subject, $i);
				}
			}
		} elseif ($this->penjepitCounter && !$this->penjepitCounter->isBuffer()) {
			$this->namespace = '';	
			$this->penjepitCounter = null;
		}
	}
}