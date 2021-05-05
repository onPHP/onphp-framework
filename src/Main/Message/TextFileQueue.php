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

use OnPHP\Main\Message\Specification\MessageQueue;

class TextFileQueue implements MessageQueue
{
	/**
	 * @var string|null
	 */
	private ?string $fileName = null;
	/**
	 * @var int|null
	 */
	private ?int $offset = 0;

	/**
	 * @return static
	 */
	public static function create(): TextFileQueue
	{
		return new static;
	}

	/**
	 * @param string $fileName
	 * @return static
	 */
	public function setFileName(?string $fileName): TextFileQueue
	{
		$this->fileName = $fileName;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getFileName(): ?string
	{
		return $this->fileName;
	}

	/**
	 * @param int $offset
	 * @return static
	 */
	public function setOffset(int $offset): TextFileQueue
	{
		$this->offset = $offset;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getOffset(): int
	{
		return $this->offset;
	}
}