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

namespace OnPHP\Main\Markup\Html;

/**
 * @ingroup Html
 * @ingroup Module
**/
final class Cdata extends SgmlToken
{
	const CDATA_STRICT_START = '<![CDATA[';
	const CDATA_STRICT_END = ']]>';

	/**
	 * @var string|null
	 */
	private ?string $data = null;
	/**
	 * @var bool
	 */
	private bool $strict	= false;

	/**
	 * @param string $data
	 * @return static
	 */
	public function setData(string $data): Cdata
	{
		$this->data = $data;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getData(): ?string
	{
		if ($this->strict) {
			return self::CDATA_STRICT_START . $this->data . self::CDATA_STRICT_END;
		} else {
			return $this->data;
		}
	}

	/**
	 * @return string|null
	 */
	public function getRawData(): ?string
	{
		return $this->data;
	}

	/**
	 * @param bool $isStrict
	 * @return static
	 */
	public function setStrict(bool $isStrict): Cdata
	{
		$this->strict = $isStrict;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isStrict(): bool
	{
		return $this->strict;
	}
}