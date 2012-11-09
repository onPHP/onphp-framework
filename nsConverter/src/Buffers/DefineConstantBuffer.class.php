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
use \Onphp\NsConverter\Business\NsConstant;

class DefineConstantBuffer implements Buffer
{
	/**
	 * @var ClassStorage
	 */
	private $classStorage = null;
	private $buffer = false;

	/**
	 * @param ClassStorage $classStorage
	 * @return DefineConstantBuffer
	 */
	public function setClassStorage(ClassStorage $classStorage)
	{
		$this->classStorage = $classStorage;
		return $this;
	}

	/**
	 * @return NamespaceBuffer
	 */
	public function init()
	{
		$this->buffer = false;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isBuffer()
	{
		return $this->buffer == true;
	}

	public function process($subject, $i)
	{
		if (!$this->buffer && is_array($subject) && $subject[0] == T_STRING && $subject[1] == 'define') {
			$this->buffer = true;
		} elseif ($this->buffer) {
			$skip = (is_string($subject) && $subject == '(');

			if ($skip) {
				/* ok, wkip this */
			} elseif (is_array($subject) && in_array($subject[0], [T_CONSTANT_ENCAPSED_STRING, T_WHITESPACE])) {
				if (preg_match('~^[\'"]([\w]+)[\'"]$~iu', $subject[1], $match)) {
					$constant = NsConstant::create()->setName($match[1]);
					$this->classStorage->addConstant($constant);
					$this->buffer = false;
				}
			} else {
				$this->buffer = false;
			}
		}
	}
}
