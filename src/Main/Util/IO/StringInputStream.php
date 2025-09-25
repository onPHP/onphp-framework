<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Util\IO;

use OnPHP\Core\Base\Assert;

/**
 * @ingroup Utils
**/
final class StringInputStream extends InputStream
{
	private string $string;
	private ?int $length    = null;

	private int $position	= 0;
	private int $mark		= 0;

	/**
	 * @param string $string
	 */
	public function __construct(string $string)
	{
		$this->string = $string;
		$this->length = strlen($string);
	}

	/**
	 * @param string $string
	 * @return self
	 */
	public static function create(string $string): StringInputStream
	{
		return new self($string);
	}

	/**
	 * @return bool
	 */
	public function isEof(): bool
	{
		return ($this->position >= $this->length);
	}

	/**
	 * @return static
	 */
	public function mark(): StringInputStream
	{
		$this->mark = $this->position;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function markSupported():bool
	{
		return true;
	}

	/**
	 * @return static
	 */
	public function reset(): StringInputStream
	{
		$this->position = $this->mark;

		return $this;
	}

	/**
	 * @return static
	 */
	public function close(): StringInputStream
	{
		$this->string = null;

		return $this;
	}

	/**
	 * @param int $count
	 * @return string|null
	 */
	public function read(int $count): ?string
	{
		if (!$this->string || $this->isEof())
			return null;

		if ($count == 1) {
			$result = $this->string[(int)$this->position];
		} else {
			$result = mb_substr($this->string, $this->position, $count);
		}

		$this->position += $count;

		return $result;
	}
}