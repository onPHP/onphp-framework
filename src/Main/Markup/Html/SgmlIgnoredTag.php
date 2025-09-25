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
**/
final class SgmlIgnoredTag extends SgmlTag
{
	/**
	 * @var Cdata|null
	 */
	private ?Cdata $cdata = null;
	/**
	 * @var string|null
	 */
	private ?string $endMark = null;

	/**
	 * @return static
	 */
	public static function comment(): SgmlIgnoredTag
	{
		return self::create()->setId('!--')->setEndMark('--');
	}

	/**
	 * @param Cdata $cdata
	 * @return static
	 */
	public function setCdata(Cdata $cdata): SgmlIgnoredTag
	{
		$this->cdata = $cdata;

		return $this;
	}

	/**
	 * @return Cdata|null
	 */
	public function getCdata(): ?Cdata
	{
		return $this->cdata;
	}

	/**
	 * @param string $endMark
	 * @return static
	 */
	public function setEndMark(string $endMark): SgmlIgnoredTag
	{
		$this->endMark = $endMark;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getEndMark(): ?string
	{
		return $this->endMark;
	}

	/**
	 * @return bool
	 */
	public function isComment(): bool
	{
		return $this->getId() == '!--';
	}

	/**
	 * @return bool
	 */
	public function isExternal(): bool
	{
		return mb_substr($this->getId() ?? '', 0, 1) == '?';
	}
}