<?php
/***************************************************************************
 *   Copyright (C) 2009 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Message;

use OnPHP\Main\Message\Specification\Message;
use OnPHP\Core\Base\Timestamp;

final class TextMessage implements Message
{
	private $timestamp	= null;
	private $text		= null;

	public static function create(Timestamp $timestamp = null)
	{
		return new self($timestamp);
	}

	public function __construct(Timestamp $timestamp = null)
	{
		$this->timestamp = $timestamp ?: Timestamp::makeNow();
	}

	public function setTimestamp(Timestamp $timestamp)
	{
		$this->timestamp = $timestamp;

		return $this;
	}

	public function getTimestamp()
	{
		return $this->timestamp;
	}

	public function setText($text)
	{
		$this->text = $text;

		return $this;
	}

	public function getText()
	{
		return $this->text;
	}
}
?>