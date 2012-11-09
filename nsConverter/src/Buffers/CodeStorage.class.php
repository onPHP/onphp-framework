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

use \Onphp\MissingElementException as MissingElementException;

class CodeStorage implements Buffer
{

	private $subjects = [];
	private $replaces = [];
	private $appends = [];

	public function count()
	{
		return count($this->subjects);
	}

	public function get($i)
	{
		if (isset($this->subjects[$i]))
			return $this->subjects[$i];

		throw new MissingElementException('not found subject with num "'.$i.'"');
	}

	/**
	 * @return CodeStorage
	 */
	public function init()
	{
		$this->subjects = [];
		$this->replaces = [];
		$this->appends = [];
		return $this;
	}

	public function toString()
	{
		$php = [];
		foreach ($this->subjects as $i => $subject) {
			if (isset($this->replaces[$i])) {
				$php[$i] = $this->replaces[$i];
			} elseif (is_array($subject)) {
				$php[$i] = $subject[1];
			} elseif (is_string($subject)) {
				$php[$i] = $subject;
			}
			if (isset($this->appends[$i])) {
				foreach ($this->appends[$i] as $append) {
					$php[$i] .= $append;
				}
			}
		}
		return implode('', $php);
	}

	public function process($subject, $i)
	{
		$this->subjects[$i] = $subject;
	}

	public function isBuffer()
	{
		return false;
	}

	/**
	 * @param string $code
	 * @param int $from
	 * @param int $to
	 * @return CodeStorage
	 */
	public function addReplace($code, $from, $to = null)
	{
		$this->replaces[$from] = $code;
		for ($i = ($from + 1); $i <= ($to ? : $from); $i++) {
			$this->replaces[$i] = '';
		}
		return $this;
	}
	
	/**
	 * @param string $code
	 * @param int $i
	 * @return CodeStorage
	 */
	public function addAppend($code, $i)
	{
		if (!isset($this->appends[$i])) {
			$this->appends[$i] = [];
		}
		$this->appends[$i][] = $code;
		return $this;
	}
}