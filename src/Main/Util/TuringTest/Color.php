<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Dmitry E. Demidov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Util\TuringTest;

use OnPHP\Core\Base\Stringable;
use OnPHP\Core\Base\Assert;

/**
 * @ingroup Turing
**/
final class Color implements Stringable
{
	private	$red	= 0;
	private	$green	= 0;
	private	$blue	= 0;

	/**
	 * @return Color
	**/
	public static function create($rgb)
	{
		static $flyweightColors = array();

		if (isset($flyweightColors[$rgb]))
			return $flyweightColors[$rgb];

		$result = new self($rgb);

		$flyweightColors[$rgb] = $result;

		return $result;
	}

	// valid values: #AABBCC, DDEEFF, A15B, etc.
	public function __construct($rgb)
	{
		$length = strlen($rgb);

		Assert::isTrue($length <= 7, 'color must be #XXXXXX');

		if ($rgb[0] == '#')
			$rgb = substr($rgb, 1);

		if ($length < 6)
			$rgb = str_pad($rgb, 6, '0', STR_PAD_LEFT);

		$this->red		= hexdec($rgb[0] . $rgb[1]);
		$this->green	= hexdec($rgb[2] . $rgb[3]);
		$this->blue		= hexdec($rgb[4] . $rgb[5]);
	}

	/**
	 * @return Color
	**/
	public function setRed($red)
	{
		$this->red = $red;

		return $this;
	}

	public function getRed()
	{
		return $this->red;
	}

	/**
	 * @return Color
	**/
	public function setGreen($green)
	{
		$this->green = $green;

		return $this;
	}

	public function getGreen()
	{
		return $this->green;
	}

	/**
	 * @return Color
	**/
	public function setBlue($blue)
	{
		$this->blue = $blue;

		return $this;
	}

	public function getBlue()
	{
		return $this->blue;
	}

	/**
	 * @return Color
	**/
	public function invertColor()
	{
		$this->setRed(255 - $this->getRed());
		$this->setBlue(255 - $this->getBlue());
		$this->setGreen(255 - $this->getGreen());

		return $this;
	}

	public function toString()
	{
		return sprintf('%02X%02X%02X', $this->red, $this->green, $this->blue);
	}
}
?>