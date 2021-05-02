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
abstract class SgmlTag extends SgmlToken
{
	/**
	 * @var string|null
	 */
	private ?string $id = null;

	/**
	 * @param string|null $id
	 * @return static
	 */
	public function setId(?string $id): SgmlTag
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getId(): ?string
	{
		return $this->id;
	}
}