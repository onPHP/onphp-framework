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

namespace OnPHP\Main\Net;

/**
 * @ingroup Net
**/
final class PercentEncodingNormalizator
{
	private $unreservedPartChars = null;

	/**
	 * @return PercentEncodingNormalizator
	**/
	public static function create()
	{
		return new self;
	}

	/**
	 * @return PercentEncodingNormalizator
	**/
	public function setUnreservedPartChars($unreservedPartChars)
	{
		$this->unreservedPartChars = $unreservedPartChars;
		return $this;
	}

	public function normalize($matched)
	{
		$char = $matched[0];
		if (mb_strlen($char) == 1) {
			if (
				!preg_match(
					'/^['.$this->unreservedPartChars.']$/u',
					$char
				)
			)
				$char = rawurlencode($char);
		} else {
			if (
				preg_match(
					'/^['.GenericUri::CHARS_UNRESERVED.']$/u',
					rawurldecode($char)
				)
			)
				$char = rawurldecode($char);
			else
				$char = strtoupper($char);
		}
		return $char;
	}
}
?>