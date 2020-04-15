<?php
/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Charts\Google;

use OnPHP\Core\Base\Assert;

/**
 * @ingroup GoogleChart
**/
final class GoogleChartLineStyle extends BaseGoogleChartStyle
{
	protected $name = 'chls';

	/**
	 * @return GoogleChartLineStyle
	**/
	public static function create()
	{
		return new self;
	}

	/**
	 * @return GoogleChartLineStyle
	**/
	public function addStyle($style)
	{
		Assert::isInstance($style, ChartLineStyle::class);

		return parent::addStyle($style);
	}

	public function toString()
	{
		$queryString = "{$this->name}=";

		Assert::isNotEmptyArray($this->styles);

		foreach ($this->styles as $style)
			$queryString .= $style->toString().'|';

		return rtrim($queryString, '|');
	}
}
?>