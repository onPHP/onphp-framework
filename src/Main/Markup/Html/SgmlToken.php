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
class SgmlToken
{
	/**
	 * @var string|null
	 */
	private ?string $value	= null;

	/**
	 * @return static
	 */
	public static function create(): SgmlToken
	{
		return new static;
	}

	/**
	 * @param string|null $value
	 * @return static
	 */
	public function setValue(?string $value): SgmlToken
	{
		$this->value = $value;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getValue(): ?string
	{
		return $this->value;
	}
}