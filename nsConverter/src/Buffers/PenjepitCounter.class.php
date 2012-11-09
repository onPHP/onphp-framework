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

class PenjepitCounter implements Buffer
{
	private $startAt = null;
	private $penjepits = 0;
	
	/**
	 * @return PenjepitCounter
	 */
	public function init()
	{
		$this->penjepits = 0;
		return $this;
	}

	public function isBuffer()
	{
		return $this->penjepits > 0;
	}
	
	public function getBufferStart()
	{
		return $this->startAt;
	}

	public function process($subject, $i)
	{
		if (is_string($subject) && $subject == '{') {
			if ($this->penjepits == 0) {
				$this->startAt = $i;
			}
			$this->penjepits++;
		} elseif (is_string($subject) && $subject == '}') {
			$this->penjepits--;
		}
	}
}